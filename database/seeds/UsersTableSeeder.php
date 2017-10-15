<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('users')->insert([
            'name'=>'admin',
            'email'=> 'admin@admin.com',
            'phone' => '123456789012',
            'password'=> bcrypt('admin')
        ]);
    }
}
