<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('asset_categories')) {
            Schema::create('asset_categories', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 100);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('assets')) {
            Schema::create('assets', function (Blueprint $table): void {
                $table->id();
                $table->string('asset_code', 50)->unique();
                $table->foreignId('asset_category_id')->nullable()->constrained('asset_categories')->nullOnDelete();
                $table->string('name', 150);
                $table->string('brand', 100)->nullable();
                $table->string('model', 100)->nullable();
                $table->string('photo_path')->nullable();
                $table->string('serial_number', 100)->nullable()->index();
                $table->string('hostname', 100)->nullable();
                $table->string('email_laptop', 150)->nullable()->index();
                $table->string('condition_status', 30)->default('GOOD');
                $table->string('asset_status', 30)->default('AVAILABLE')->index();
                $table->foreignId('current_user_id')->nullable()->constrained('users')->nullOnDelete()->index();
                $table->foreignId('current_pt_id')->nullable()->constrained('pts')->nullOnDelete();
                $table->date('purchase_date')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('asset_movements')) {
            Schema::create('asset_movements', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
                $table->string('movement_type', 30);
                $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('to_user_id')->nullable()->constrained('users')->nullOnDelete()->index();
                $table->foreignId('from_pt_id')->nullable()->constrained('pts')->nullOnDelete();
                $table->foreignId('to_pt_id')->nullable()->constrained('pts')->nullOnDelete();
                $table->string('condition_before', 30)->nullable();
                $table->string('condition_after', 30)->nullable();
                $table->date('movement_date');
                $table->string('handover_document_path')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['asset_id', 'movement_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_movements');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_categories');
    }
};
