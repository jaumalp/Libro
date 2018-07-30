@extends('layouts.app')

@section('content')


    @foreach ($todos as $uno)
            <p><?php
                $este = \App\User::find($uno);
                echo $este['id'].": ".$este['name']."<br>";
            ?></p>
    @endforeach


@endsection
