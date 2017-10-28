<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('supplier_id');
            $table->string('delivery_code')->default('')->comment('送货单号');
            $table->date('invoice_date')->default('1000-01-01')->comment('发票日期');
            $table->string('invoice_code')->default('');
            $table->string('invoice_amount')->default('');
            $table->float('arrears')->default(0)->comment('欠款');
            $table->string('note')->default('');
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('suppliers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchases');
    }
}
