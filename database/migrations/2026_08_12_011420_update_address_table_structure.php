<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**

     */
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('address');

            $table->string('name')->after('id');
            $table->string('province')->after('name');
            $table->string('city')->after('province');
            $table->string('subdistrict')->after('city');
            $table->string('postal_code', 10)->after('subdistrict');
            $table->text('detail')->after('postal_code');
            $table->string('phone', 20)->after('detail');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'province',
                'city',
                'subdistrict',
                'postal_code',
                'detail',
                'phone',
            ]);

            $table->text('address')->nullable();
        });
    }
};