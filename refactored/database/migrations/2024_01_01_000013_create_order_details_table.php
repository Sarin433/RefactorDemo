<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id('detail_id');
            $table->string('order_number', 50);
            $table->foreign('order_number')
                  ->references('order_number')
                  ->on('orders')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            $table->string('product_number', 50);
            $table->foreign('product_number')
                  ->references('product_number')
                  ->on('products')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2); // price snapshot at order time
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
