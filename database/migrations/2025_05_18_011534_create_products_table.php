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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('status');
            $table->timestamp('imported_t')->useCurrent();
            $table->string('url')->nullable();
            $table->string('creator')->nullable();
            $table->timestamp('created_t')->useCurrent();
            $table->timestamp('last_modified_t')->useCurrent();
            $table->string('product_name');
            $table->string('quantity')->nullable();
            $table->string('brands')->nullable();
            $table->string('categories')->nullable();
            $table->string('labels')->nullable();
            $table->string('cities')->nullable();
            $table->string('purchase_places')->nullable();
            $table->string('stores')->nullable();
            $table->text('ingredients_text')->nullable();
            $table->string('traces')->nullable();
            $table->string('serving_size')->nullable();
            $table->float('serving_quantity')->nullable();
            $table->float('nutriscore_score')->nullable();
            $table->string('nutriscore_grade')->nullable();
            $table->string('main_category')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
