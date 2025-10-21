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
        Schema::create('web_configuration', function (Blueprint $table) {
            $table->id();
            $table->string('telefono_huancayo');
            $table->string('telefono_lima');
            $table->string('email');
            $table->string('horario_atencion');
            $table->string('tiktok');
            $table->string('instagram');
            $table->string('facebook');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_configuration');
    }
};
