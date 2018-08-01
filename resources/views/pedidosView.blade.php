@extends('layouts/app')

@section('title')Pedir @endsection

@section('content')
    <div class='principal'>adasdasddaas
        <form class="form-control" action="{{ action('PedidoController@add') }}">
            @csrf
            <div class="input-group date">
                <input type="text" class="form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
            </div>



        </form>
    </div>


@endsection
