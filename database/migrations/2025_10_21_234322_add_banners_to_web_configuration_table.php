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
            // Banner 1
            $table->string('banner_1_imagen')->nullable()->after('facebook');
            $table->string('banner_1_titulo')->nullable()->after('banner_1_imagen');
            $table->text('banner_1_texto')->nullable()->after('banner_1_titulo');
            $table->string('banner_1_link')->nullable()->after('banner_1_texto');
            
            // Banner 2
            $table->string('banner_2_imagen')->nullable()->after('banner_1_link');
            $table->string('banner_2_titulo')->nullable()->after('banner_2_imagen');
            $table->text('banner_2_texto')->nullable()->after('banner_2_titulo');
            $table->string('banner_2_link')->nullable()->after('banner_2_texto');
            
            // Banner 3
            $table->string('banner_3_imagen')->nullable()->after('banner_2_link');
            $table->string('banner_3_titulo')->nullable()->after('banner_3_imagen');
            $table->text('banner_3_texto')->nullable()->after('banner_3_titulo');
            $table->string('banner_3_link')->nullable()->after('banner_3_texto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('web_configuration', function (Blueprint $table) {
            $table->dropColumn([
                'banner_1_imagen', 'banner_1_titulo', 'banner_1_texto', 'banner_1_link',
                'banner_2_imagen', 'banner_2_titulo', 'banner_2_texto', 'banner_2_link',
                'banner_3_imagen', 'banner_3_titulo', 'banner_3_texto', 'banner_3_link'
            ]);
        });
    }
};
