<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        factory(\App\User::class)->create(['name'=>'Jaime', 'email'=>'jaumalp@gmail.com']);
        factory(\App\User::class, 20)->create();
        factory(\App\Pedido::class,100)->create();
    }
}
