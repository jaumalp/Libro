<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function pedidos(){
        return $this->hasMany(Pedido::class);
    }



    /*
        Devuelve -> Collection de Users
                 -> Todos los usuarios odenados por $porCampo
    */
    public static function todosOrdenados($porCampo = 'id'){
        return collect(User::all()->sortBy($porCampo));
    }



    /*
        Devuelve -> Collection de Users
                 -> Usuarios que no piden nada en ciclo $id_ciclo
    */
    public static function noPidenNadaEnCiclo($id_ciclo){
        $todos = User::todosOrdenados();
        $piden = collect();
        $peticiones = Pedido::sobreCicloTodos($id_ciclo);
        $pedidores = $peticiones->pluck('user');

        return collect($todos->diff($pedidores));
    }

}
