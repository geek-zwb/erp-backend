<?php

use Illuminate\Database\Seeder;

class WarehousesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('warehouses')->insert([
            [
                'id' => 1,
                'name' => '家',
                'status' => 1,
                'note' => '采购单将存入此库存',
            ], [
                'id' => 2,
                'name' => '亚马逊',
                'status' => 1,
                'note' => '发货单将消耗此库存',
            ]
        ]);
    }
}
