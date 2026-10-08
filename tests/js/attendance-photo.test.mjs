import assert from 'node:assert/strict';
import { existsSync } from 'node:fs';
import test from 'node:test';

const helperPath = new URL('../../public/js/attendance-photo.js', import.meta.url);

test('memberi tahu UI sebelum selfie dikodekan', async () => {
    assert.ok(existsSync(helperPath), 'Helper foto presensi belum tersedia.');

    const { captureAttendancePhoto } = await import(helperPath.href);
    const events = [];
    const photo = { name: 'selfie' };
    const context = {
        translate: () => events.push('mirror'),
        scale: () => events.push('mirror'),
        drawImage: () => events.push('draw'),
    };
    const canvas = {
        width: 0,
        height: 0,
        getContext: () => context,
        toBlob: (callback, type, quality) => {
            assert.deepEqual(events, ['preparing', 'mirror', 'mirror', 'draw']);
            assert.equal(type, 'image/jpeg');
            assert.equal(quality, 0.75);
            callback(photo);
        },
    };

    const result = await captureAttendancePhoto({
        video: { videoWidth: 640, videoHeight: 480 },
        canvas,
        mirror: true,
        onPreparing: () => events.push('preparing'),
    });

    assert.equal(canvas.width, 640);
    assert.equal(canvas.height, 480);
    assert.equal(result, photo);
});

test('mengecilkan selfie besar sebelum diunggah', async () => {
    const { captureAttendancePhoto } = await import(helperPath.href);
    const canvas = {
        width: 0,
        height: 0,
        getContext: () => ({ drawImage: () => {} }),
        toBlob: (callback) => callback({ size: 1 }),
    };

    await captureAttendancePhoto({
        video: { videoWidth: 4000, videoHeight: 3000 },
        canvas,
    });

    assert.equal(canvas.width, 1280);
    assert.equal(canvas.height, 960);
});

test('menolak bila selfie tidak dapat dikodekan', async () => {
    assert.ok(existsSync(helperPath), 'Helper foto presensi belum tersedia.');

    const { captureAttendancePhoto } = await import(helperPath.href);

    await assert.rejects(
        captureAttendancePhoto({
            video: { videoWidth: 640, videoHeight: 480 },
            canvas: {
                getContext: () => ({ drawImage: () => {} }),
                toBlob: (callback) => callback(null),
            },
        }),
        /Foto gagal disiapkan/,
    );
});
