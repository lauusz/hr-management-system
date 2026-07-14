<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('atk_stock_movements', function (Blueprint $table): void {
            if (! Schema::hasColumn('atk_stock_movements', 'unit_price')) {
                $table->unsignedBigInteger('unit_price')->nullable()->after('qty');
            }
            if (! Schema::hasColumn('atk_stock_movements', 'total_price')) {
                $table->unsignedBigInteger('total_price')->nullable()->after('unit_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('atk_stock_movements', function (Blueprint $table): void {
            if (Schema::hasColumn('atk_stock_movements', 'unit_price')) {
                $table->dropColumn('unit_price');
            }
            if (Schema::hasColumn('atk_stock_movements', 'total_price')) {
                $table->dropColumn('total_price');
            }
        });
    }
};
