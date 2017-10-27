<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_id');
            $table->string('order_code')->comment('订单额外编号，如亚马逊订单号')->nullable();
            $table->string('status')->default('待发货'); // 订单状态
            $table->date('delivery_date')->nullable()->comment('发货日期');
            $table->string('delivery_code')->comment('发货单号');
            $table->string('delivery_company')->comment('发货快递');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers');

            $table->index('order_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
