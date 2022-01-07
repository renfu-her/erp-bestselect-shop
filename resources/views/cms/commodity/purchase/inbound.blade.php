@extends('layouts.main')
@section('sub-content')

    <h2 class="mb-3">入庫 <a href="{{ Route('cms.purchase.edit', ['id' => $id]) }}">回採購單</a></h2>

    {{var_dump($inboundList)}}
    <BR><BR><BR>
    {{var_dump($inboundOverviewList)}}
@endsection
