export function captureAttendancePhoto({ video, canvas, mirror = false, onPreparing = () => {} }) {
    if (!video?.videoWidth || !video?.videoHeight) {
        return Promise.reject(new Error('Kamera belum siap.'));
    }

    onPreparing();

    const scale = Math.min(1, 1280 / Math.max(video.videoWidth, video.videoHeight));
    canvas.width = Math.round(video.videoWidth * scale);
    canvas.height = Math.round(video.videoHeight * scale);

    const context = canvas.getContext('2d');

    if (!context) {
        return Promise.reject(new Error('Foto gagal disiapkan.'));
    }

    if (mirror) {
        context.translate(canvas.width, 0);
        context.scale(-1, 1);
    }

    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    return new Promise((resolve, reject) => {
        canvas.toBlob((blob) => {
            if (blob) {
                resolve(blob);
                return;
            }

            reject(new Error('Foto gagal disiapkan.'));
        }, 'image/jpeg', 0.75);
    });
}
