<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('produto_custo_estrutura_mysql', 'unid_med')) {
            Schema::table('produto_custo_estrutura_mysql', function (Blueprint $table) {
                $table->string('unid_med', 10)->nullable()->after('custo_percentual');
                $table->decimal('base_m2', 15, 6)->default(0)->after('unid_med');
                $table->decimal('base_m3', 15, 6)->default(0)->after('base_m2');
                $table->decimal('fator_unid', 15, 6)->default(1)->after('base_m3');
            });
        }
    }

    public function down(): void
    {
        Schema::table('produto_custo_estrutura_mysql', function (Blueprint $table) {
            $table->dropColumn(['unid_med', 'base_m2', 'base_m3', 'fator_unid']);
        });
    }
};
