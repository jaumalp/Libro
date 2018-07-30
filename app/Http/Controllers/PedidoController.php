<?php

namespace App\Http\Controllers;

use App\Pedido;
use App\User;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public static function algoDelCiclo($id_ciclo){
        $todos = Pedido::sobreCiclo($id_ciclo, false);
        return view('pedidos',compact('todos'));
    }

    public static function nadaDelCiclo($id_ciclo){
        return view('usuarios',['todos' => Pedido::usersQueNoPidenCiclo($id_ciclo)]);
    }
}
