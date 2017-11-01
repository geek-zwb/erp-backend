<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(UnitsTableSeeder::class);
        $this->call(TypesTableSeeder::class);
        $this->call(SuppliersTableSeeder::class);
        $this->call(WarehousesTableSeeder::class);
        $this->call(ProductsTableSeeder::class);
    }
}
