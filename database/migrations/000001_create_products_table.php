<?php

use Illuminate\Database\Migrations\Migration;
use Mongodb\Laravel\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('code');
            $table->string('status');
            $table->timestamp('imported_t')->nullable();
            $table->string('url');
            $table->string('creator');
            $table->bigInteger('created_t');
            $table->bigInteger('last_modified_t');
            $table->string('product_name');
            $table->string('quantity')->nullable();
            $table->string('brands')->nullable();
            $table->text('categories')->nullable();
            $table->text('labels')->nullable();
            $table->string('cities')->nullable();
            $table->string('purchase_places')->nullable();
            $table->string('stores')->nullable();
            $table->text('ingredients_text')->nullable();
            $table->text('traces')->nullable();
            $table->string('serving_size')->nullable();
            $table->float('serving_quantity')->nullable();
            $table->integer('nutriscore_score')->nullable();
            $table->string('nutriscore_grade')->nullable();
            $table->string('main_category')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
