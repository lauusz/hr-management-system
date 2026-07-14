<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_access_roles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 50);
            $table->timestamps();

            $table->unique(['user_id', 'role']);
            $table->index('role');
        });

        Schema::create('atk_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100)->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('atk_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('atk_category_id')->nullable()->constrained('atk_categories')->nullOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('unit_name', 30);
            $table->unsignedInteger('unit_size')->default(1);
            $table->string('content_unit_name', 30)->default('pcs');
            $table->unsignedInteger('stock_qty')->default(0);
            $table->unsignedInteger('minimum_stock')->default(0);
            $table->unsignedInteger('min_request_qty')->default(1);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['atk_category_id', 'is_active']);
            $table->index('name');
            $table->index('stock_qty');
        });

        Schema::create('atk_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('request_number', 30)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('user_name_snapshot');
            $table->foreignId('pt_id')->nullable()->constrained('pts')->nullOnDelete();
            $table->string('pt_name_snapshot')->nullable();
            $table->string('status', 30)->default('PENDING');
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['pt_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('atk_request_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('atk_request_id')->constrained('atk_requests')->cascadeOnDelete();
            $table->foreignId('atk_item_id')->constrained('atk_items')->restrictOnDelete();
            $table->unsignedInteger('qty');
            $table->string('item_name_snapshot', 150);
            $table->string('unit_name_snapshot', 30);
            $table->unsignedInteger('unit_size_snapshot')->default(1);
            $table->string('content_unit_name_snapshot', 30)->default('pcs');
            $table->timestamps();

            $table->index('atk_request_id');
            $table->index('atk_item_id');
        });

        Schema::create('atk_stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('atk_item_id')->constrained('atk_items')->restrictOnDelete();
            $table->string('movement_type', 30);
            $table->unsignedInteger('qty');
            $table->unsignedBigInteger('unit_price')->nullable();
            $table->unsignedBigInteger('total_price')->nullable();
            $table->unsignedInteger('stock_before');
            $table->unsignedInteger('stock_after');
            $table->string('source_type', 30)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['atk_item_id', 'created_at']);
            $table->index('movement_type');
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('atk_need_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('user_name_snapshot');
            $table->foreignId('pt_id')->nullable()->constrained('pts')->nullOnDelete();
            $table->string('pt_name_snapshot')->nullable();
            $table->foreignId('atk_item_id')->nullable()->constrained('atk_items')->nullOnDelete();
            $table->string('requested_item_name', 150);
            $table->unsignedInteger('qty');
            $table->string('unit_name', 30);
            $table->text('reason');
            $table->string('status', 30)->default('PENDING');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['pt_id', 'status']);
            $table->index('atk_item_id');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atk_need_requests');
        Schema::dropIfExists('atk_stock_movements');
        Schema::dropIfExists('atk_request_items');
        Schema::dropIfExists('atk_requests');
        Schema::dropIfExists('atk_items');
        Schema::dropIfExists('atk_categories');
        Schema::dropIfExists('user_access_roles');
    }
};
