@extends('hanoivip::layouts.app')

@section('title', 'Pay via Mercado')

@section('content')

@if (!empty($guide))
	<p>{{$guide}}</p>
@endif

@if (!empty($data))
	<a href="{{$data['checkoutUrl']}}">Pay Via Mercado</a>
@else
	<p>{{__('hanoivip.mercado::error')}}</p>
@endif

@endsection
