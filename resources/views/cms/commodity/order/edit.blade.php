@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">訂單編號-{{ $sn }}</h2>

    <form id="form1" method="post" action="">
        @method('POST')
        @csrf

        @error('id')
        <div class="alert alert-danger mt-3">{{ $message }}</div>
        @enderror

        <div class="card shadow p-4 mb-4">
            
        </div>

        <div class="card shadow p-4 mb-4">
            
        </div>


        <div id="submitDiv">
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">列印整張訂購單</button>
                <a href="" class="btn btn-outline-primary px-4"
                   role="button">返回列表</a>
            </div>
        </div>
    </form>

@endsection
@once
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endonce

