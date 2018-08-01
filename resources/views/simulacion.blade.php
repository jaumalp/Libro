@extends('layouts/app')

@section('title')Simulación de Ciclo @endsection

@section('content')
    <div class='principal'>
        {{\App\expresaElRetDeSimulacion($ret)}}
    </div>
@endsection
