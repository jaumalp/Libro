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
        $faker = Faker\Factory::create('es_ES');
        factory(\App\User::class)->create(['name'=>'Jaime', 'email'=>'jaumalp@gmail.com']);
        factory(\App\User::class, 10)->create();
        factory(\App\Pedido::class,300)->create(['estado'=>'1']);
        factory(\App\Pedido::class,4)->create(['ciclo'=>4, 'tipo'=>1]);
        factory(\App\Pedido::class,2)->create(['ciclo'=>4, 'tipo'=>2]);
        factory(\App\Pedido::class,20)->create(['ciclo'=>4, 'tipo'=>0]);


        for ($x=1;$x<10;$x++)
            factory(\App\Pedido::class,30)->create(['ciclo'=>'-'.$x, 'estado'=>1]);
    }
}
