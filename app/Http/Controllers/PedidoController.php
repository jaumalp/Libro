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

    public function simular($id){
        $libro = new Libro(4,3);
        $libro->setCicloId($id);
        $ret = $libro->simulaAsignacion();
        showPeticiones($ret[1]);
        showPeticiones($ret[2]);
    }

    public function pruebas($id,$segPar){
        //dd($probar);

        $libro = new Libro($segPar,$segPar);
        $libro->setCicloId($id);
        $ret = Pedido::limpiaErroresBBDD();
        $ret = $libro->simulaAsignacion();

        expresaElRetDeSimulacion($ret);
    }
}
