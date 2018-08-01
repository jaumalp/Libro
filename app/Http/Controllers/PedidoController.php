<?php

namespace App\Http\Controllers;

use App\Pedido;
use function App\showPeticiones;
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
        $ret = $libro->simulaAsignacion();
        showPeticiones($ret[1]);
    }





    public function pruebas($id,$segPar){
        //dd($probar);

        $libro = new Libro($segPar,$segPar);
        $libro->setCicloId($id);
        $ret = Pedido::limpiaErroresBBDD();
        $ret = $libro->simulaAsignacion();


        showPeticiones($ret[1]);

    }
}
