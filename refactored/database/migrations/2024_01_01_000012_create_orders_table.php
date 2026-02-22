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
        Schema::create('orders', function (Blueprint $table) {
            $table->string('order_number', 50)->primary();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            $table->unsignedBigInteger('status_id')->default(1);
            $table->foreign('status_id')
                  ->references('status_id')
                  ->on('status_references')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            $table->text('shipping_address')->nullable();
            $table->timestamp('order_date')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
