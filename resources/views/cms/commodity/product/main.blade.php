@extends('layouts.main')
@section('sub-content')
<div class="mb-3">
    <h2>主商品名稱22</h2>
</div>

@endsection
@once
    @push('sub-styles')
    <style>
        .icon.-close_eye + span.label::before {
            content: '不';
        }
    </style>
    @endpush
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endOnce
