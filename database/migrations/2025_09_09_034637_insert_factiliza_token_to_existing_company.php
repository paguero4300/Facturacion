<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Company;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Actualizar la empresa existente con el token de Factiliza
        $company = Company::first();
        
        if ($company) {
            $company->update([
                'factiliza_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIzOTM1OCIsImh0dHA6Ly9zY2hlbWFzLm1pY3Jvc29mdC5jb20vd3MvMjAwOC8wNi9pZGVudGl0eS9jbGFpbXMvcm9sZSI6ImNvbnN1bHRvciJ9.plpdJ5q1ZS2W1iRNEIkzOc3qj-vMaHxrjFRerWlVHfM'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover el token de Factiliza
        $company = Company::first();
        
        if ($company) {
            $company->update([
                'factiliza_token' => null
            ]);
        }
    }
};
