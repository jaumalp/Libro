<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


class Pedido extends Model
{
    /* BelongsTo User de Pedido */
    public function user(){
        return $this->belongsTo(User::class);
    }

    /* Devuelve collection : Pedidos sobre ciclo con estado = $asignados */
    public static function sobreCiclo($id_ciclo, $estado = 0){
        return collect( Pedido::where('ciclo',$id_ciclo)->where('estado',$asignados)->get() );
    }

    public static function sobreCicloQuePideSinAsignar($id_ciclo, $que_pide){
        return collect( self::sobreCiclo($id_ciclo, 0)->where('que_pide',$que_pide));
    }

    /* Devuelve collection : Todos los pedidos sobre el ciclo */
    public static function sobreCicloTodos($id_ciclo){
        return collect( Pedido::where('ciclo',$id_ciclo)->where('estado',0)->get() );
    }


    public static function bajasSobreCiclo($id_ciclo){
        return collect( Pedido::where('ciclo',$id_ciclo)->where('tipo',2)->where('estado',0)->get() );
    }

    public static function licenciasSobreCiclo($id_ciclo){
        return collect( Pedido::where('ciclo',$id_ciclo)->where('tipo',1)->where('estado',0)->get() );
    }

    public static function limpiaErroresBBDD($iteraciones = 6){
        for ($x=0;$x<$iteraciones;$x++){
            self::eliminaDuplicados();
            self::siPidesCicloNoPideNadaMas();
            self::diasAbsorbenJornadasMismoTipoMismoDia();
            self::mañanaTardesMismoTipoMismoDia();
            self::dosDiasYNocheIgualCiclo();
        }
    }

    public static function siPidesCicloNoPideNadaMas(){
        $sql = "SELECT p1.id,p1.que_pide,p2.id as id2,p2.que_pide as que_pide2 FROM pedidos as p1,pedidos as p2 WHERE ";
        $sql .= "p1.ciclo=p2.ciclo AND p1.user_id=p2.user_id AND ";
        $sql .= "(p1.que_pide=0 AND p2.que_pide!=0)";
        $temp = DB::select($sql);
        $ret = [0,0];
        foreach($temp as $par_de_ids){
            if (isset($par_de_ids->id2)){
                $pedido_temp2 = Pedido::find($par_de_ids->id2);
                if ($pedido_temp2!=null){
                    $pedido_temp2->delete();
                    $ret[0]++;
                }
            }
        }
        return $ret;
    }

    public static function mañanaTardesMismoTipoMismoDia(){
        $sql = "SELECT p1.id,p2.id as id2 FROM pedidos as p1,pedidos as p2 WHERE ";
        $sql .= "p1.ciclo=p2.ciclo AND p1.tipo=p2.tipo AND p1.user_id=p2.user_id AND ";
        $sql .= "(p1.que_pide=3 AND p2.que_pide=4)";
        $temp = DB::select($sql);
        $ret = [0,0];
        foreach($temp as $par_de_ids){
            if (isset($par_de_ids->id) and isset($par_de_ids->id2)) {
                $pedido_temp1 = Pedido::find($par_de_ids->id);
                $pedido_temp2 = Pedido::find($par_de_ids->id2);
                if ($pedido_temp1!=null and $pedido_temp2!=null){
                    $pedidoAMeter = $pedido_temp1->replicate();
                    $pedidoAMeter->push();
                    $pedidoAMeter->que_pide = 1;
                    $pedidoAMeter->save();
                    $pedido_temp1->delete();
                    $pedido_temp2->delete();
                    $ret[0] += 2;
                    $ret[1] += 1;
                }
            }
        }

        $sql = "SELECT p1.id,p2.id as id2 FROM pedidos as p1,pedidos as p2 WHERE ";
        $sql .= "p1.ciclo=p2.ciclo AND p1.tipo=p2.tipo AND ";
        $sql .= "(p1.que_pide=5 AND p2.que_pide=6)";
        $temp = DB::select($sql);
        foreach($temp as $par_de_ids){
            if (isset($par_de_ids->id) and isset($par_de_ids->id2)) {
                $pedido_temp1 = Pedido::find($par_de_ids->id);
                $pedido_temp2 = Pedido::find($par_de_ids->id2);
                if ($pedido_temp1!=null and $pedido_temp2!=null) {
                    $pedidoAMeter = $pedido_temp1->replicate();
                    $pedidoAMeter->push();
                    $pedidoAMeter->que_pide = 2;
                    $pedidoAMeter->save();
                    $pedido_temp1->delete();
                    $pedido_temp2->delete();
                    $ret[0] += 2;
                    $ret[1] += 1;
                }
            }
        }

        return $ret;
    }

    public static function diasAbsorbenJornadasMismoTipoMismoDia(){
        $sql = "SELECT p1.id,p2.id as id2 FROM pedidos as p1,pedidos as p2 WHERE ";
        $sql .= "p1.ciclo=p2.ciclo AND p1.tipo=p2.tipo AND p1.user_id=p2.user_id AND ";
        $sql .= "(p1.que_pide=1 AND (p2.que_pide=4 OR p2.que_pide=3))";
        $temp = DB::select($sql);
        $ret = [0,0];
        foreach($temp as $par_de_ids){
            if (isset($par_de_ids->id) and isset($par_de_ids->id2)) {
                $pedido_temp1 = Pedido::find($par_de_ids->id);
                $pedido_temp2 = Pedido::find($par_de_ids->id2);
                if ($pedido_temp1!=null and $pedido_temp2!=null){
                    $pedido_temp2->delete();
                    $ret[0]++;
                }
            }
        }

        $sql = "SELECT p1.id,p2.id as id2 FROM pedidos as p1,pedidos as p2 WHERE ";
        $sql .= "p1.ciclo=p2.ciclo AND p1.tipo=p2.tipo AND ";
        $sql .= "(p1.que_pide=2 AND (p2.que_pide=5 OR p2.que_pide=6))";
        $temp = DB::select($sql);
        foreach($temp as $par_de_ids){
            if (isset($par_de_ids->id) and isset($par_de_ids->id2)) {
                $pedido_temp1 = Pedido::find($par_de_ids->id);
                $pedido_temp2 = Pedido::find($par_de_ids->id2);
                if ($pedido_temp1!=null and $pedido_temp2!=null){
                    $pedido_temp2->delete();
                    $ret[0]++;
                }
            }
        }

        return $ret;
    }

    public static function eliminaDuplicados(){
        $sql = "SELECT p1.id,p2.id as id2 FROM pedidos as p1,pedidos as p2 WHERE ";
        $sql .= "p1.ciclo=p2.ciclo AND p1.id!=p2.id AND ";
        $sql .= "p1.que_pide=p2.que_pide AND p1.user_id=p2.user_id";
    //    dd($sql);
        $temp = DB::select($sql);
        $ret = [0,0];
        foreach($temp as $par_de_ids){
            if (isset($par_de_ids->id) and isset($par_de_ids->id2)) {
                $pedido_temp1 = Pedido::find($par_de_ids->id);
                $pedido_temp2 = Pedido::find($par_de_ids->id2);
                if ($pedido_temp1!=null and $pedido_temp2!=null){
                    $pedidoAMeter = $pedido_temp1->replicate();
                    $pedidoAMeter->push();
                    $pedidoAMeter->que_pide = 1;
                    $pedidoAMeter->save();
                    $pedido_temp1->delete();
                    $pedido_temp2->delete();
                    $ret[0] += 2;
                    $ret[1]++;
                }
            }
        }
        return $ret;
    }

    public static function dosDiasYNocheIgualCiclo(){
        $sql = "SELECT p1.id,p1.que_pide,p2.id as id2,p2.que_pide,p3.id as id3,p3.que_pide,p1.ciclo,p2.ciclo,p3.ciclo ";
        $sql .= "FROM pedidos as p1,pedidos as p2, pedidos as p3 WHERE p1.user_id=p2.user_id AND p1.user_id=p3.user_id AND ";
        $sql .= "p1.ciclo=p2.ciclo AND p2.ciclo=p3.ciclo AND p1.id!=p2.id AND p2.id!=p3.id AND ";
        $sql .= "p1.que_pide=1 AND p2.que_pide=2 AND p3.que_pide=7";

        $temp = DB::select($sql);
        $ret = [0,0];
        foreach($temp as $par_de_ids){
            if (isset($par_de_ids->id) and isset($par_de_ids->id2) and isset($par_de_ids->id3)) {
                $pedido_temp1 = Pedido::find($par_de_ids->id);
                $pedido_temp2 = Pedido::find($par_de_ids->id2);
                $pedido_temp3 = Pedido::find($par_de_ids->id3);
                if ($pedido_temp1!=null and $pedido_temp2!=null and $pedido_temp3!=null){
                    $pedidoAMeter = $pedido_temp1->replicate();
                    $pedidoAMeter->push();
                    $pedidoAMeter->que_pide = 0;
                    $pedidoAMeter->save();
                    $pedido_temp1->delete();
                    $pedido_temp2->delete();
                    $pedido_temp3->delete();
                    $ret[0] += 3;
                    $ret[1]++;
                }
            }
        }
        return $ret;
    }
}
