@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">入庫審核</h2>
    <x-b-pch-navi :id="$id"></x-b-pch-navi>

    {{var_dump($inboundList)}}
    <BR><BR><BR>
    {{var_dump($inboundOverviewList)}}
@endsection
