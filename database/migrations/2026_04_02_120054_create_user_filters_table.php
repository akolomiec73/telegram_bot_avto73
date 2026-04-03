<?php

declare(strict_types=1);

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
        Schema::create('user_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_bot_user')->constrained('bot_users');
            $table->boolean('filter_status')->default(false);
            $table->integer('filter_price_min')->nullable();
            $table->integer('filter_price_max')->nullable();
            $table->boolean('filter_category_car')->default(false);
            $table->boolean('filter_category_detail')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_filters');
    }
};
