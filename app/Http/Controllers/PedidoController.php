<?php

namespace App\Http\Controllers;

use function App\expresaElRetDeSimulacion;
use App\Pedido;
use function App\showPeticiones;
use App\User;
use Illuminate\Http\Request;
use App\Libro;
use Illuminate\Support\Collection;


class PedidoController extends Controller
{
    public function simular($id_ciclo,$huecos_dia,$huecos_noche){
        $libro = new Libro($huecos_dia,$huecos_noche);
        $libro->setCicloId($id_ciclo);
        Libro::limpiaBBDD();
        $ret = $libro->simulaAsignacion();
        return view("simulacion",compact("ret"));
    }

    public function add(){
        return view('pedidosView');
    }
}
