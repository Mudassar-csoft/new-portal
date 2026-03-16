<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventory_items')) {
            return;
        }

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
            $table->string('item_code')->unique();
            $table->string('category');
            $table->string('item_name');
            $table->string('brand')->nullable();
            $table->string('model_no')->nullable();
            $table->string('serial_no')->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->string('unit', 40)->default('pcs');
            $table->unsignedInteger('minimum_stock')->default(0);
            $table->string('condition_status', 40)->default('good');
            $table->string('room_location')->nullable();
            $table->date('purchase_date')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['campus_id', 'category']);
            $table->index(['campus_id', 'quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
