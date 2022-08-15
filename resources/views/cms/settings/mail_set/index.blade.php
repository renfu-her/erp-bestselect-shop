@extends('layouts.main')
@section('sub-content')
    <div class="card">
        <div class="card-header">
            通知信管理
        </div>
        <form class="card-body" method="post" action="{{ $formAction }}">
            @method('POST')
            @csrf
            @foreach($mail_set_list as $key => $mail_set)
                <x-b-form-group name="title" title="{{ $mail_set->title }}" required="true">
                    <input type="hidden" value="{{ $mail_set->category }}" name="category[]" class="form-control form-control-sm text-center" readonly>
                    <input type="hidden" value="{{ $mail_set->event }}" name="event[]" class="form-control form-control-sm text-center" readonly>
                    <input type="hidden" value="{{ $mail_set->feature }}" name="feature[]" class="form-control form-control-sm text-center" readonly>
                    <input type="hidden" value="{{ $mail_set->type }}" name="type[]" class="form-control form-control-sm text-center" readonly>
                    <label style="display:none">{{ App\Enums\Globals\SharedPreference\Type::getDescription($mail_set->type) }}</label>
                    <label>&nbsp;&nbsp;&nbsp;&nbsp;</label>
                    @if(App\Enums\Globals\SharedPreference\Type::offon()->value == $mail_set->type)
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input type="radio" name="status[{{$key}}]" class="d-target r-target form-check-input @error('status') is-invalid @enderror"
                                       value="{{ App\Enums\Globals\StatusOffOn::Off()->value }}" {{ old('status.'. $key, $mail_set->status ?? '') == App\Enums\Globals\StatusOffOn::Off()->value ? 'checked' : '' }} >
                                {{ App\Enums\Globals\StatusOffOn::getDescription(App\Enums\Globals\StatusOffOn::Off()->value) }}
                            </label>
                        </div>

                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input type="radio" name="status[{{$key}}]" class="d-target r-target form-check-input @error('status') is-invalid @enderror"
                                       value="{{ App\Enums\Globals\StatusOffOn::On()->value }}" {{ old('status.'. $key, $mail_set->status ?? '') == App\Enums\Globals\StatusOffOn::On()->value ? 'checked' : '' }} >
                                {{ App\Enums\Globals\StatusOffOn::getDescription(App\Enums\Globals\StatusOffOn::On()->value) }}
                            </label>
                        </div>
                    @endif
                </x-b-form-group>
            @endforeach

            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
            </div>
        </form>
    </div>

@endsection
