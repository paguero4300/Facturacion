<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('warehouses', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete(); // ya existe
            $t->string('code', 32);
            $t->string('name', 255);
            $t->boolean('is_default')->default(false);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->softDeletes();

            $t->unique(['company_id','code'], 'uniq_company_code');
            $t->index(['company_id','is_active'], 'idx_company_active');
        });
    }

    public function down(): void {
        Schema::dropIfExists('warehouses');
    }
};
