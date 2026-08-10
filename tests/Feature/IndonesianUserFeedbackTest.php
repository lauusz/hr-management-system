<?php

use Illuminate\Support\Facades\Route;

it('uses Indonesian pagination feedback', function () {
    expect(__('pagination.previous'))->toBe('Sebelumnya')
        ->and(__('pagination.next'))->toBe('Berikutnya')
        ->and(__('Showing'))->toBe('Menampilkan')
        ->and(__('results'))->toBe('hasil');
});

it('uses clear Indonesian names for operational fields', function () {
    $errors = validator([], [
        'qty' => ['required'],
        'admin_note' => ['required'],
        'deletion_note' => ['required'],
        'unit_name' => ['required'],
    ])->errors();

    expect($errors->first('qty'))->toBe('Jumlah wajib diisi.')
        ->and($errors->first('admin_note'))->toBe('Catatan admin wajib diisi.')
        ->and($errors->first('deletion_note'))->toBe('Alasan penghapusan wajib diisi.')
        ->and($errors->first('unit_name'))->toBe('Satuan wajib diisi.');
});

it('shows Indonesian feedback for missing pages', function () {
    Route::get('/_test/missing-page', fn () => abort(404));

    $this->get('/_test/missing-page')
        ->assertNotFound()
        ->assertSee('Halaman Tidak Ditemukan');
});
