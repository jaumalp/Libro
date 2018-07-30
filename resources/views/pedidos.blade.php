@extends('layouts.app')

@section('content')<?php
    $uno = $todos->first();

    $names = $uno->getConnection()->getSchemaBuilder()->getColumnListing($uno->getTable());
    ?>
    <p><table style="border: 2px solid red; margin-left: 50px"><tr>
    @foreach ($names as $uno)
            <td style="border: 1px solid blue; padding: 3px 10px">{{$uno}}</td>
    @endforeach

        </tr>

    @foreach ($todos as $uno)
        <?php $atributos = $uno->getAttributes(); ?>
        <tr>
            @foreach( $atributos as $campo)
                    <td style="border: 1px solid blue; padding: 3px 10px">{{$campo}}</td>
            @endforeach
        </tr>
    @endforeach
    </table></p>

@endsection
