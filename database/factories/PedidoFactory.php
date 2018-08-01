<?php

use Faker\Generator as Faker;

$factory->define(App\Pedido::class, function (Faker $faker) {
    return [
        'ciclo'=>$faker->numberBetween(0,3),
        'user_id'=>$faker->numberBetween(0,10),
        'tipo'=>$faker->numberBetween(0,2),
        'que_pide'=>$faker->numberBetween(0,7),
        'estado'=>0
    ];
});
