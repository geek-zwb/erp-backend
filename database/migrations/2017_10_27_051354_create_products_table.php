<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('sku')->unique()->comment('产品唯一编号');
            $table->unsignedInteger('unit_id');
            $table->unsignedInteger('type_id');
            $table->double('weight')->default(0)->comment('产品重量');
            $table->string('description')->default('');
            $table->string('picture')->default('');
            $table->string('note')->default('');
            $table->timestamps();

            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('type_id')->references('id')->on('types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
