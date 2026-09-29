<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('category_product')) {
            Schema::create('category_product', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('category_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['product_id', 'category_id']);
            });

            // Backfill existing product category_id into pivot table
            if (Schema::hasTable('products') && Schema::hasColumn('products', 'category_id')) {
                $now = now();
                $products = DB::table('products')->whereNotNull('category_id')->get(['id', 'category_id']);
                $inserts = [];
                foreach ($products as $p) {
                    $inserts[] = [
                        'product_id'  => $p->id,
                        'category_id' => $p->category_id,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ];
                }
                if (!empty($inserts)) {
                    DB::table('category_product')->insertOrIgnore($inserts);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('category_product');
    }
};
