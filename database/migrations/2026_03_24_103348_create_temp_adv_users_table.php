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
        Schema::create('temp_adv_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_bot_user')->constrained('bot_users');
            $table->string('adv_category', 255)->nullable();
            $table->string('adv_car_mark', 255)->nullable();
            $table->integer('adv_car_year_realise')->nullable();
            $table->integer('adv_price')->nullable();
            $table->text('adv_description')->nullable();
            $table->string('adv_photo', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_adv_users');
    }
};
