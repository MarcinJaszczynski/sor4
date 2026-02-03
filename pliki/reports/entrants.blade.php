@extends('layouts.app')
@section('content')

@php

$accepted = $data->where('eventStatus', '!=', 'Anulowana')
            ->where('eventStatus', '!=', 'oferta')
            ->where('eventStatus', '!=', 'Zapytanie')
            ->where('eventStatus', '!=', 'doanulacji');

$canceled =  $data->where('eventStatus', '!=', 'Zapytanie')
            ->where('eventStatus', '!=', 'oferta')
            ->where('eventStatus', '!=', 'Potwierdzona')
            ->where('eventStatus', '!=', 'OdprawaOK')
            ->where('eventStatus', '!=', 'Zakończona')
            ->where('eventStatus', '!=', 'Archiwum');

$planned =  $data->where('eventStatus', '!=', 'Anulowana')
            ->where('eventStatus', '!=', 'doanulacji')
            ->where('eventStatus', '!=', 'Potwierdzona')
            ->where('eventStatus', '!=', 'OdprawaOK')
            ->where('eventStatus', '!=', 'Zakończona')
            ->where('eventStatus', '!=', 'Archiwum');

@endphp



<div class="container">
    <h1 class="mb-3"> Raport uczestników - index</h1> 

    <div class="row">
        <div class="col mb-3">
        <form action="/reports/entrants">
            @csrf
        <label for="start" class="awesome">Start: </label>
        <input type="date" name="start" id="start" class="datetime date_input">
        <label for="start" class="awesome"> Koniec: </label>
        <input type="date" name="end" id="end" class="date_input">
        <button type="submit" class="btn btn-success">Wyślij</button>
        </form>
    </div>
    <div class="row" id="resoults">
        <div>Start: <span id="dataStart">{{$request->start}}</span>
            Koniec: <span id="dataEnd">{{$request->end}}</span></div>
    </div>
    <div class="row mt-3          ">
        <div class="col">
            <h4>Zaakceptowane</h4>
        <div>Imprezy: {{$accepted->count()}}</div>

        <div>Uczestnicy: {{$accepted->sum("eventTotalQty")}}</div>
        <hr>
        @isset($data)
            @foreach($accepted as $event)
            <div>{{$event->eventStartDateTime}}<br><a href="/events/{{$event->id}}/edit">{{$event->eventName}}</a><br>{{$event->eventStatus}}<br>os. {{$event->eventTotalQty}}
                
                @foreach($event->eventContractor as $econtractor)
                    @if($econtractor->contractortype_id === 4)
                     <br><a href="/contractors/{{ $econtractor->contractor->id }}/edit" class="small ">{!! $econtractor->contractor->name !!}</a>, <br>
                     {{$econtractor->contractor->firstname}} {{$econtractor->contractor->surname}}
                    @endif
                @endforeach
                
                
        <br>
            </div><hr>
            @endforeach
        @endisset
        </div>
        <div class="col">
            <h4>Planowane</h4>
        <div>Imprezy: {{$planned->count()}}</div>

        <div>Uczestnicy: {{$planned->sum("eventTotalQty")}}</div>
        <hr>
        @isset($data)
            @foreach($planned as $event)
            <div>{{$event->eventStartDateTime}}<br><a href="/events/{{$event->id}}/edit">{{$event->eventName}}</a><br>{{$event->eventStatus}}<br> os. {{$event->eventTotalQty}}
                @foreach($event->eventContractor as $econtractor)
                    @if($econtractor->contractortype_id === 4)
                     <br><a href="/contractors/{{ $econtractor->contractor->id }}/edit" class="small ">{!! $econtractor->contractor->name !!}</a>, <br>
                     {{$econtractor->contractor->firstname}} {{$econtractor->contractor->surname}}
                    @endif
                @endforeach
            </div><hr>
            @endforeach
        @endisset
        </div>
        <div class="col">
            <h4>Odwołane</h4>
            <div>Imprezy: {{$canceled->count()}}</div>
        <div>Uczestnicy: {{$canceled->sum("eventTotalQty")}}</div>
        <hr>
        @isset($data)
            @foreach($canceled as $event)
            <div>{{$event->eventStartDateTime}}<br><a href="/events/{{$event->id}}/edit">{{$event->eventName}}</a><br>{{$event->eventStatus}}<br>os. {{$event->eventTotalQty}}
                @foreach($event->eventContractor as $econtractor)
                    @if($econtractor->contractortype_id === 4)
                     <br><a href="/contractors/{{ $econtractor->contractor->id }}/edit" class="small ">{!! $econtractor->contractor->name !!}</a>, <br>
                     {{$econtractor->contractor->firstname}} {{$econtractor->contractor->surname}}
                    @endif
                @endforeach</div><hr>
            @endforeach
        @endisset
        </div>
    </div>
    <div class="row">
        <div class="col">

        </div>
    </div>

</div>

<script>
    // let resoultsfield = document.querySelector('#resoults');
    // let input_start_field = document.querySelector('#start');
    // let input_end_field = document.querySelector('#end');

    // let start_field = resoults.querySelector("#dataStart");
    // let end_field = resoults.querySelector("#dataEnd");

    // input_start_field.addEventListener('click', function(){
    //     let startVal = input_start_field.value;
    //     start_field.innerText = 'dupa';
    // })



</script>
 
@endsection