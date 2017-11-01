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
            $table->string('name')->comment('订货标识');
            $table->unsignedInteger('supplier_id');
            $table->string('delivery_code')->default('')->comment('送货单号');
            $table->date('invoice_date')->default('1000-01-01')->comment('发票日期');
            $table->string('invoice_code')->default('');
            $table->decimal('invoice_amount',10, 3)->default(0);
            $table->decimal('delivery_amount',10, 3)->default(0)->comment('该采购单运费');
            $table->decimal('arrears',10, 3)->default(0)->comment('欠款');
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
