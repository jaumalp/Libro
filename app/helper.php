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
    $que_pides = ["Ciclo","Dia 1","Dia 2","Mañana 1","Tarde 1","Mañana 2","Tarde 2","Noche"];
    $tipos = ['Normal', 'Baja', 'Licencia'];

    echo "<table class='tabla_ejemplo' style='text-align: center'>
<tr'><td> ID_PEDIDO </td><td> USER </td><td> SE LLEVA </td><td> MOTIVO </td></tr>";
    foreach ($peticiones as $p){
        echo "<tr><td>$p->id</td><td>$p->user_id</td><td>".$que_pides[$p->que_pide]."</td>";
        echo "<td>".$tipos[$p->tipo]."</td></tr>";

    }
    echo "</table>";
}

function disminuyeHuecos($huecos_temp, $que_pide, $por_baja = false){
    $diagramaRestasPorBaja = [ [1,1,1,1,1,1,1,1], [1,1,0,1,1,0,0,0],
        [1,0,1,0,0,1,1,0], [1,1,0,1,0,0,0,0], [1,1,0,0,1,0,0,0],
        [1,0,1,0,0,1,0,0], [1,0,1,0,0,0,1,0], [1,0,0,0,0,0,0,1],
    ];

    $diagramaRestasNormal = [ [1,1,1,1,1,1,1,1], [0,1,0,1,1,0,0,0],
        [0,0,1,0,0,1,1,0], [0,1,0,1,0,0,0,0], [0,1,0,0,1,0,0,0],
        [0,0,1,0,0,1,0,0], [0,0,1,0,0,0,1,0], [0,0,0,0,0,0,0,1],
    ];

    if  ($por_baja)
        $diagramaARestar = $diagramaRestasPorBaja[$que_pide];
    else
        $diagramaARestar = $diagramaRestasNormal[$que_pide];

    return restarArrays($huecos_temp,$diagramaARestar);
}


function expresaElRetDeSimulacion($ret){
    $que_pides = ["Ciclo","Dia 1","Dia 2","Mañana 1","Tarde 1","Mañana 2","Tarde 2","Noche"];

    if ($ret[0]!=null) {
        if ($ret[0]) {
            echo "<p><b>SE LLEVAN DIAS:</b></p>";
            showPeticiones($ret[1]);
            if ($ret[2]->count() > 0) {
                foreach ($ret[2] as $q) {
                    echo "No hay huecos para : " . $que_pides[$q]."<br>";
                }
            } else {
                echo "Huecos para todos<br>";
            }

            if ($ret[3]->count() > 0) {
                foreach ($ret[3] as $q) {
                    echo "No se pide nadie : " . $que_pides[$q]."<br>";
                }
            } else {
                echo "Todo se pide<br>";
            }

            echo "<p><b>NO SE LLEVAN LO PEDIDO</b></p>";
            showPeticiones($ret[4]);

            echo "Despues de todas la asignaciones quedan los huecos: <br>";
            echo json_encode($ret[5]);
        } else {
            "No se ha resuelto por un empate: <br>";
            foreach ($ret[1] as $user_id) {
                $user_id . " - ";
            }
        }
    }else {
        echo "MEGA ERROR!!!";
    }

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

    public function devuelveHuecosPorRepeticion($asignados, $huecos_antes){
        $todas = $asignados->count();
        $sinRepetidos = $asignados->groupby("user_id")->count();
        return $huecos_antes[0] + $todas-$sinRepetidos;
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
            $sql .= " OR (tipo=2 AND que_pide IN $especialPorBaja[$que_se_pide])) ORDER BY ciclo DESC";


            $data = DB::select($sql);
            $datos = collect($data);
            $seriales = collect();


            ($seriales = $datos->groupBy('user_id'));
            $comparativa = collect();
            foreach ($seriales as $user_id =>$serial) {
                $impl = $serial->implode('ciclo',"|");
                $comparativa[$user_id] = collect($serial);

            }

            $comparativaSinRepetidos = collect();

            foreach ($comparativa as $unoConMuchasPeticiones) {
                $unique = $unoConMuchasPeticiones->map(function ($item, $key) {
                    return $item->ciclo;
                });
                $unique = $unique->unique();
                $comparativaSinRepetidos->put($unoConMuchasPeticiones->first()->user_id, $unique);
            }

            /*  Ahora tenemos el collection con arrays dentro pero para hacer que la funcion MIN devuelva
                el valor correcto debo poner todos los arrays con igual tamaño, asi que los igualo de tamaño
                al mayor de ellos y los rellenos con valores muy bajos para que no influyan */
            $countMayor = 0;
            foreach ($comparativaSinRepetidos as $unArray)
                if ($countMayor<$unArray->count())
                    $countMayor = $unArray->count();


            $filtered = collect();
            foreach ($comparativaSinRepetidos as $key => $unArray){
                $filtered->put($key,$unArray->pad($countMayor, -9999));
            }

            //METODO POR COLECCION
            $comparativaSinRepetidos = collect($filtered);

            $repes = $this->get_duplicates($comparativaSinRepetidos);
            if ($repes!=[]) {
                return [false,$repes];
            } else {
                $ret = collect();
                //$huecos_temp = $huecos;  // REAL
                $huecos_temp = 1; // FALSO
                //OK, ahora mientras haya huecos se van dando
                while($comparativaSinRepetidos->count()>0 and $huecos_temp>0){
                    $ganadorDelHueco = $comparativaSinRepetidos->min();
                    $key2 = $comparativaSinRepetidos->search($ganadorDelHueco);
                    $comparativaSinRepetidos->pull($key2);
                    $huecos_temp--;
                    foreach ($peticiones as $una){
                        if ($una->user_id == $key2) {
                            $meter = $una;
                        }
                    };
                    $ret->push($meter);
                }
                return [true,$ret];
            }
            return [false,null];
        }
    }

    /* Devuelve array de Users_id */
    function get_duplicates( $arrayDeArrays ) {
        $array = [];
        foreach ($arrayDeArrays as $key => $valor)
            $array[$key] = collect($valor)->implode("*");

        //tengo un array con "-2*-3*-6"...
        $keys_rep = [];
        $array_unique_de_uno = array_unique($array);
        $unArrayDeRepes = array_diff_assoc($array,$array_unique_de_uno);
        $ret=[];
        foreach ($array as $key => $valor){
            if (in_array($valor,$unArrayDeRepes)){
                array_push($ret,$key);
            }
        }
        return $ret;
    }


    /*  Devuelve un super Array:
                FUE BIEN        SUPER EMPATE                    ERROR EXTRAÑO
        [0]     True            False                           NULL
        [1]     $asignados      $empatados                      NULL
        [2]     $noHayHuecos    NULL                            NULL
        [3]     $noSePide       NULL                            NULL
        [4]     $noSeLlevan     NULL                            NULL
        [5]     $huecos_temp    NULL                            NULL            */
    public function simulaAsignacion(){
        //INICIO DE VARIABLES
        $todos = \App\User::todosOrdenados();
        $no_piden = \App\User::noPidenNadaEnCiclo($this->ciclo);
        $si_piden = $todos->diff($no_piden);
        $this->rellenaHuecos();
        $todos_pedidos = Pedido::sobreCicloTodos($this->ciclo, true);
        $asignados = collect();
        $noHayHuecos = collect();
        $noSePide = collect();

        $bajasYLicencias = Pedido::bajasSobreCiclo($this->ciclo); /* Collection */
        $bajasYLicencias = $bajasYLicencias->concat(Pedido::LicenciasSobreCiclo($this->ciclo));

        $asignados = $asignados->concat($bajasYLicencias);
        $huecos_temp = $this->huecosInvariable->toArray();

        foreach ($bajasYLicencias as $baja)
            $huecos_temp = disminuyeHuecos($huecos_temp, $baja->que_pide, true);

        $todos = $bajasYLicencias->count();
        $sinRepetidos = $bajasYLicencias->groupby("user_id")->count();
        $huecos_temp[0] += $todos-$sinRepetidos; /* Devuelve los huecos por duplicados de Bajas */

        echo json_encode($huecos_temp);


        $siguen_pidiendo = $todos_pedidos->diff($asignados);

        for($x=0;$x<8;$x++){ /* ahora por orden se van dando los huecos */
            $lasPeticiones = $siguen_pidiendo->where('que_pide',$x);
            $cuantos_piden = $lasPeticiones->count();

            if ($cuantos_piden==0){
                //NADIE SE PIDE ESTO
                $noSePide->push($x);
            } else if ($huecos_temp[$x]<1){
                //NO HAY HUECOS
                $noHayHuecos->push($x);
            } else if ($huecos_temp[$x]<$cuantos_piden){
                //A PELEAR POR LOS HUECOS
                $resultado = $this->dirimeEmpates($lasPeticiones,$huecos_temp[$x]);
                if ($resultado[0]) {
                    //DESEMPATADO OK
                    $losGanadores = $resultado[1];
                    $asignados = $asignados->concat($losGanadores);
                    foreach ($losGanadores as $unPedidoGanador)
                        $huecos_temp = disminuyeHuecos($huecos_temp, $x, false);
                } else if ($resultado[0]->isnull()) {
                    //FALLO INESPERADO REVISAR
                    return [null, null, null, null, null, null];
                } else {
                    //NO SE PUDIERON DESEMPATAR $resultado[1] (Peticiones)
                    return [false, $resultado[1], null, null, null, null];
                }
            } else { //HUECOS SUFICIENTES, TODOS SE ASIGNAN
                $asignados = $asignados->concat($lasPeticiones);
                foreach($lasPeticiones as $una)
                    $huecos_temp = disminuyeHuecos($huecos_temp, $x, false);
            }
        }
        //Si hemos llegado hasta aqui, la variable $asignados tiene a los afortunados
        //$noSePide y $noHayHuecos son autoexplicativas...
        for($x=0;$x<8;$x++)
            if ($huecos_temp[$x]<0)
                $huecos_temp[$x] = 0;

        $noSeLlevan = $todos_pedidos->diff($asignados);
        return [true, $asignados, $noHayHuecos, $noSePide, $noSeLlevan, $huecos_temp];
    }
}
