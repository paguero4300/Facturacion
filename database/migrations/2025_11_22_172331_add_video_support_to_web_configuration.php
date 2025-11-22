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
        Schema::table('web_configuration', function (Blueprint $table) {
            // Tipo de media para cada banner (image, video)
            $table->enum('banner_1_type', ['image', 'video'])->default('image')->after('banner_1_imagen');
            $table->string('banner_1_video')->nullable()->after('banner_1_type');
            
            $table->enum('banner_2_type', ['image', 'video'])->default('image')->after('banner_2_imagen');
            $table->string('banner_2_video')->nullable()->after('banner_2_type');
            
            $table->enum('banner_3_type', ['image', 'video'])->default('image')->after('banner_3_imagen');
            $table->string('banner_3_video')->nullable()->after('banner_3_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_configuration', function (Blueprint $table) {
            $table->dropColumn([
                'banner_1_type',
                'banner_1_video',
                'banner_2_type',
                'banner_2_video',
                'banner_3_type',
                'banner_3_video',
            ]);
        });
    }
};
