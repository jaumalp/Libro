<?php

namespace App\Http\Controllers;

use App\Pedido;
use App\User;
use Illuminate\Http\Request;
use App\Libro;
use Illuminate\Support\Collection;


class PedidoController extends Controller
{
    public static function algoDelCiclo($id_ciclo){
        $todos = Pedido::sobreCiclo($id_ciclo, false);
        return view('pedidos',compact('todos'));
    }

    public static function nadaDelCiclo($id_ciclo){
        return view('usuarios',['todos' => Pedido::usersQueNoPidenCiclo($id_ciclo)]);
    }

    public function simular($id){
        $libro = new Libro(4,3);
        $libro->setCicloId($id);
        $libro->simulaAsignacion();
    }





    public function pruebas($id,$segPar){
        $libro = new Libro($segPar,$segPar);
        $libro->setCicloId($id);

        //$libro->simulaAsignacion();
        Pedido::mañanaTardesMismoTipoMismoDia();
    }
}
