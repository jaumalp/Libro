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
    public static function sobreCiclo($id_ciclo, $asignados = 1){
        return collect( Pedido::where('ciclo',$id_ciclo)->where('estado',$asignados)->get() );
    }

    public static function sobreCicloQuePideSinAsignar($id_ciclo, $que_pide){
        return collect( self::sobreCiclo($id_ciclo, 0)->where('que_pide',$que_pide));
    }

    /* Devuelve collection : Todos los pedidos sobre el ciclo */
    public static function sobreCicloTodos($id_ciclo){
        return collect( Pedido::where('ciclo',$id_ciclo)->get() );
    }


    public static function bajasSobreCiclo($id_ciclo){
        return collect( Pedido::where('ciclo',$id_ciclo)->where('tipo',2)->get() );
    }

    public static function licenciasSobreCiclo($id_ciclo){
        return collect( Pedido::where('ciclo',$id_ciclo)->where('tipo',1)->get() );
    }

    public static function mañanaTardesMismoTipoMismoDia(){
        $sql = "SELECT p1.id,p2.id as id2 FROM pedidos as p1,pedidos as p2 WHERE ";
        $sql .= "p1.ciclo=p2.ciclo AND p1.tipo=p2.tipo AND ";
        $sql .= "(p1.que_pide=3 AND p2.que_pide=4)";
        $temp = DB::select($sql);
        foreach($temp as $par_de_ids){
            if (isset($par_de_ids->id) and isset($par_de_ids->id2)) {
                $pedido_temp1 = Pedido::find($par_de_ids->id);
                $pedido_temp2 = Pedido::find($par_de_ids->id2);
                $pedidoAMeter = $pedido_temp1->replicate();
                $pedidoAMeter->que_pide = 1;
                $pedidoAMeter->save();
                $pedido_temp1->delete();
                $pedido_temp2->delete();
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
                $pedidoAMeter = $pedido_temp1->replicate();
                $pedidoAMeter->que_pide = 2;
                $pedidoAMeter->save();
                $pedido_temp1->delete();
                $pedido_temp2->delete();
            }
        }
    }
}
