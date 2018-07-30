<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pedido extends Model
{
    public function user(){
        return $this->belongsTo(User::class);
    }

    public static function sobreCiclo($id_ciclo, $asignados = 1){

        $ret = Pedido::where('ciclo',$id_ciclo)->where('estado',$asignados)->get();

        return $ret;
    }

    public static function sobreCicloTodos($id_ciclo){

        $ret = Pedido::where('ciclo',$id_ciclo)->get();

        return $ret;
    }

    public static function usersTodosIdOrdenados($id_ciclo){
        $todos_usuarios = User::all('id')->toArray();

        for($x = 0;$x<count($todos_usuarios);$x++)
            $nueva[$x] = $todos_usuarios[$x]['id'];

        sort($nueva);
        return $nueva;

    }

    public static function usersQueNoPidenCiclo($id_ciclo){
        $todos = self::usersTodosIdOrdenados($id_ciclo);
        $piden = [];
        $x = 0;
        $peticiones = self::sobreCicloTodos($id_ciclo);
        //dd($peticiones);
        foreach ($peticiones as $peticion){
            $piden[$x] = (int)$peticion->User['id'];
            $x++;
        }
        return array_diff($todos,$piden); /* Array con IDs que no piden nada */
    }
}
