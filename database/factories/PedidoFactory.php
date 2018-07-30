<?php

use Faker\Generator as Faker;

$factory->define(App\Pedido::class, function (Faker $faker) {
    return [
        'ciclo'=>$faker->numberBetween(-3,3),
        'user_id'=>$faker->numberBetween(0,10),
        'tipo'=>$faker->numberBetween(0,2),
        'que_pide'=>$faker->numberBetween(0,7)
    ];
});
