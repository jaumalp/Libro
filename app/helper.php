<?php
/**
 * Created by PhpStorm.
 * User: Jaime
 * Date: 30/07/2018
 * Time: 23:10
 */

namespace App;

use App\Pedido;
use function foo\func;
use Illuminate\Support\Collection;
use test\Mockery\Fixtures\ClassWithAllLowerCaseMethod;
use Illuminate\Support\Facades\DB;


function restarArrays($minuendo, $sustraendo) {
    $ret = [];
    for ($x=0; $x<count($minuendo);$x++) {
        $ret[$x] = $minuendo[$x] - $sustraendo[$x];
    }
    return $ret;
}

function showPeticiones($peticiones){
    $que_pides = ["C","D1","D2","M1","T1","M2","T2","N"];
    $tipos = ['Normal', 'Baja', 'Licencia'];

    foreach ($peticiones as $p){
        echo "(User:$p->user_id se pide ".$que_pides[$p->que_pide]." por ";
        echo $tipos[$p->tipo].")<br>";
    }
}

function disminuyeHuecos($huecos_temp, $que_pide, $por_baja = false){
    $diagramaRestasPorBaja = [ [1,1,1,1,1,1,1,1], [1,1,0,1,1,0,0,0],
        [1,0,1,0,0,1,1,0], [1,1,0,1,0,0,0,0], [1,1,0,0,1,0,0,0],
        [1,0,1,0,0,1,0,0], [1,0,1,0,0,0,1,0], [1,0,0,0,0,0,0,1],
    ];

    $diagramaRestasNormal = [ [1,1,1,1,1,1,1,1], [0,1,0,1,1,0,0,0],
        [0,0,1,0,0,1,1,0], [0,0,0,1,0,0,0,0], [0,0,0,0,1,0,0,0],
        [0,0,0,0,0,1,0,0], [0,0,0,0,0,0,1,0], [0,0,0,0,0,0,0,1],
    ];

    if  ($por_baja)
        $diagramaARestar = $diagramaRestasPorBaja[$que_pide];
    else
        $diagramaARestar = $diagramaRestasNormal[$que_pide];

    return restarArrays($huecos_temp,$diagramaARestar);
}


class Libro {
    public $seVanDia, $seVanNoche, $ciclo;

    private $diagramaRestasPorBaja = [ [1,1,1,1,1,1,1,1], [1,1,0,1,1,0,0,0],
        [1,0,1,0,0,1,1,0], [1,1,0,1,0,0,0,0], [1,1,0,0,1,0,0,0],
        [1,0,1,0,0,1,0,0], [1,0,1,0,0,0,1,0], [1,0,0,0,0,0,0,1] ]; /* Arrays de restas para los huecos que quitan al pedirse  */
    private $diagramaRestasNormal = [ [1,1,1,1,1,1,1,1], [0,1,0,1,1,0,0,0],
        [0,0,1,0,0,1,1,0], [0,0,0,1,0,0,0,0], [0,0,0,0,1,0,0,0],
        [0,0,0,0,0,1,0,0], [0,0,0,0,0,0,1,0], [0,0,0,0,0,0,0,1] ];  /* algo del ciclo siendo el orden: [C D1 D2 M1 T1 M2 T2 N] */

    private $huecosInvariable;
    private $que_pides = ["C","D1","D2","M1","T1","M2","T2","N"];

    public function __construct($dia = 9, $noche = 10){
        $this->setCuantosSeVan($dia,$noche);
    }

    public function setCuantosSeVan($dia, $noche){
        $this->seVanDia = $dia;
        $this->seVanNoche = $noche;
    }

    public function setCicloId($id){
        $this->ciclo = $id;
    }

    private function rellenaHuecos(){
        $this->huecosInvariable = collect();
        for ($x = 0; $x < 7; $x++)
            $this->huecosInvariable->push($this->seVanDia);
        $this->huecosInvariable->push($this->seVanNoche);
    }

    public static function UneMañanaTardes(){
        return Pedido::mañanaTardesMismoTipoMismoDia();
    }

    /* Devuelve Collection de Pedidos de los asignados segun huecos que haya */
    function dirimeEmpates($peticiones, $huecos){
        $especialPorBaja = [ '(0)',
            '(0,1,2)', '(0,1,2)',
            '(0,1,2,3,4,5,6)','(0,1,2,3,4,5,6)','(0,1,2,3,4,5,6)','(0,1,2,3,4,5,6)',
            '(0,7)'
        ];

        $especialPorPeticion = [ '(0)', //Pedir un ciclo solo te penalizara otro ciclo
            '(1,2)', '(1,2)', //Pedir un dia te penaliza otro dia
            '(3,4,5,6)','(3,4,5,6)','(3,4,5,6)','(3,4,5,6)', //Jornadas
            '(7)'//Solo la Noche
        ];

        if ($peticiones->count()==0){
            return collect();
        } else if ($huecos>=$peticiones->count())
            return $peticiones;
        else {
            $id_ciclo = $peticiones->first()->ciclo;
            $que_se_pide = $peticiones->first()->que_pide;
            $peticionesAnteriores = collect();

            $sinRepetir = $peticiones->unique();
            $temp = $peticiones->pluck('user_id');
            $sql = "SELECT * FROM pedidos WHERE user_id in (".$temp->implode(',').")";
            $sql .= " AND estado='1' AND ciclo < $id_ciclo AND (que_pide IN $especialPorPeticion[$que_se_pide]";
            $sql .= " OR (tipo=2 AND que_pide IN $especialPorBaja[$que_se_pide])   )";
            dd($sql);
        }

    }

    //$sql = "SELECT id FROM pedidos WHERE estado=1 AND (que_pide=7 OR user_id=5 OR user_id=7)";
    //$petAnteDeUnTio = DB::select($sql);


    public function simulaAsignacion($ejecutarRealidad = false){

        $todos = \App\User::todosOrdenados();
        $no_piden = \App\User::noPidenNadaEnCiclo($this->ciclo);
        $si_piden = $todos->diff($no_piden);
        $this->rellenaHuecos();
        $todos_pedidos = Pedido::sobreCicloTodos($this->ciclo);
        $asignados = new Collection();

        $bajasYLicencias = Pedido::bajasSobreCiclo($this->ciclo); /* Collection */
        $bajasYLicencias = $bajasYLicencias->concat(Pedido::LicenciasSobreCiclo($this->ciclo));

        $asignados = $asignados->concat($bajasYLicencias);
        $huecos_temp = $this->huecosInvariable->toArray();

        foreach ($bajasYLicencias as $baja)
            $huecos_temp = disminuyeHuecos($huecos_temp, $baja->que_pide, true);

        /* Huecos despues de la baja */

        /* Aqui se puede mostrar: $huecos despues de licencias y bajas y asignados por ellos...*/
        $todos = $bajasYLicencias->count();
        $sinRepetidos = $bajasYLicencias->groupby("user_id")->count();
        $huecos_temp[0] += $todos-$sinRepetidos;

        showPeticiones($bajasYLicencias);
        echo "<br>Quedan ".json_encode($huecos_temp)." huecos<br>";


        $siguen_pidiendo = $todos_pedidos->diff($asignados);

        for($x=0;$x<8;$x++){ /* ahora por orden se van dando los huecos */
            $lasPeticiones = Pedido::sobreCicloQuePideSinAsignar($this->ciclo,$x);
            $cuantos_piden = $lasPeticiones->count();
            if ($cuantos_piden==0){
                echo "NO SE PIDE NADIE: [".$this->que_pides[$x]."]<br>";
            } else if ($huecos_temp[$x]==0){
                echo "NO HAY HUECOS: [".$this->que_pides[$x]."]<br>";
            } else if ($huecos_temp[$x]<$cuantos_piden){ //A pelear por los huecos
                echo "EMPATES: [".$this->que_pides[$x]."] ";
                $this->dirimeEmpates($lasPeticiones,$huecos_temp[$x]);
                echo "<br>";
            } else { //Huecos suficientes, todos se asignan...
                echo "SI[".$this->que_pides[$x]."]: Huecos (".$huecos_temp[$x].") - (".$cuantos_piden.") Peticiones<br>";
                $asignados = $asignados->concat($lasPeticiones);
                $huecos_temp = disminuyeHuecos($huecos_temp, $x, false);
            }
        }






        /*if ($ejecutarRealidad) {
            foreach ($asignados as $uno) {
                $unPedidoQueSeraAsignado = Pedido::find($uno[3]);
                $unPedidoQueSeraAsignado->estado = 1;
                $unPedidoQueSeraAsignado->save();
            }
        }

        $usuarios = getCampoAsCollection($asignados, 0);*/


        // Vale Ya estan las bajas dadas... ahora el desempate por el resto de huecos

    }

}
