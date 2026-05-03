<?php

// ⚠️ PERINGATAN: JANGAN gunakan LazilyRefreshDatabase atau RefreshDatabase
// karena akan men-trigger migrate:fresh yang menghapus SEMUA data di database.
// Gunakan DatabaseTransactions agar setiap test di-rollback tanpa menghapus data existing.

use Illuminate\Foundation\Testing\DatabaseTransactions;

pest()->extend(Tests\TestCase::class)
    ->use(DatabaseTransactions::class)
    ->in('Feature');
