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
        Schema::table('virtual_museum_object', function (Blueprint $table) {
            $table->string('slot_mesh_name')->nullable()->after('mesh_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('virtual_museum_object', function (Blueprint $table) {
            $table->dropColumn('slot_mesh_name');
        });
    }
};
