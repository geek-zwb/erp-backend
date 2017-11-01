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
            'id' => 1,
            'name'=>'家',
            'status'=> 1,
            'note' => '',
        ],[
            'id' => 2,
            'name'=>'亚马逊',
            'status'=> 1,
            'note' => '',
        ]);
    }
}
