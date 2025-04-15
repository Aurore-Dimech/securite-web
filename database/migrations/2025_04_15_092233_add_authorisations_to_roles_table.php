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
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('can_add_product')->default(false);
            $table->boolean('can_get_my_product')->default(false);
            $table->boolean('can_get_my_bestsellers')->default(false);
            $table->boolean('can_get_products')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('can_add_product');
            $table->dropColumn('can_get_my_product');
            $table->dropColumn('can_get_my_bestsellers');
            $table->dropColumn('can_get_products');
        });
    }
};
