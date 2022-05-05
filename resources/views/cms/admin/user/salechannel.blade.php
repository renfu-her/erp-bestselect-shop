@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.user.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>

    <form method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf
        <div class="card mb-4">
            <div class="card-header">通路權限</div>
            <div class="card-body">
              
                @foreach ($channels as $key => $channel)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="channel_id[]" id="role{{ $key }}"
                            @if (in_array($channel['id'], old('channel_id', $current_channel ?? []))) checked @endif value="{{ $channel['id'] }}">
                        <label class="form-check-label" for="role{{ $key }}">
                            {{ $channel['title'] }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
        </div>
    </form>
@endsection
@once
    @push('sub-scripts')
       
    @endpush
@endonce
