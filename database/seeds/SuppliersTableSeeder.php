<?php

use Illuminate\Database\Seeder;

class SuppliersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('suppliers')->insert([
            [
                'id' => 1,
                'name' => '上海英陆',
                'address' => '上海',
                'phone' => '',
                'note' => '',
            ], [
                'id' => 2,
                'name' => '瑞安市博迪汽配有限公司',
                'address' => '瑞安市',
                'phone' => '',
                'note' => '',
            ],[
                'id' => 3,
                'name' => '瑞安市祺泰汽车电子厂',
                'address' => '瑞安市',
                'phone' => '',
                'note' => '',
            ],[
                'id' => 4,
                'name' => '上海昭临',
                'address' => '上海',
                'phone' => '',
                'note' => '',
            ],[
                'id' => 5,
                'name' => '明俊',
                'address' => '',
                'phone' => '',
                'note' => '',
            ],
        ]);
    }
}
