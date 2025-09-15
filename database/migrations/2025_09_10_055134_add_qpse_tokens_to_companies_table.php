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
        Schema::table('companies', function (Blueprint $table) {
            $table->text('qpse_config_token')->nullable()->after('ose_password')->comment('Token de configuración QPse para crear empresa');
            $table->text('qpse_access_token')->nullable()->after('qpse_config_token')->comment('Token de acceso QPse para operaciones');
            $table->timestamp('qpse_token_expires_at')->nullable()->after('qpse_access_token')->comment('Fecha de expiración del token de acceso');
            $table->json('qpse_last_response')->nullable()->after('qpse_token_expires_at')->comment('Última respuesta de QPse para debug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'qpse_config_token',
                'qpse_access_token', 
                'qpse_token_expires_at',
                'qpse_last_response'
            ]);
        });
    }
};
