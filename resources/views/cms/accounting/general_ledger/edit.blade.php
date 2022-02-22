@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">會計科目代碼:
        {{ $data->code ?? ''}}
    </h2>
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.general_ledger.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回會計科目列表
        </a>
    </div>
    <div class="card shadow p-4 mb-4">
        <form class="card-body" method="post" action="{{ $formAction }}">
            @method('POST')
            @csrf
            <x-b-form-group name="name" title="會計科目名稱" required="true">
                <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', (isset($data) ? ($data->name ?? ''): '')) }}" />
            </x-b-form-group>
            @if($isFourthGradeExist === false)
                <x-b-form-group name="has_next_grade" title="次科目" required="true">
                    <div class="px-1">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                開放
                                <input class="form-check-input @error('has_next_grade') is-invalid @enderror"
                                       value="1"
                                       name="has_next_grade"
                                       type="radio"
                                       required
                                       @if ($method === 'edit' &&
                                            isset($data) ? ($data->has_next_grade === 1 ? true : false) : false
                                            )
                                       checked
                                    @endif
                                >
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                關閉
                                <input class="form-check-input @error('has_next_grade') is-invalid @enderror"
                                       value="0"
                                       name="has_next_grade"
                                       type="radio"
                                       required
                                       @if ( $method === 'edit' &&
                                            isset($data) ? ($data->has_next_grade === 1 ? true : false) : false
                                            )
                                       checked
                                    @endif
                                >
                            </label>
                        </div>
                    </div>
                </x-b-form-group>
            @endif

            <div class="col-12 col-sm-4 mb-3 invoice_date">
                <label class="form-label">使用公司</label>
                <select class="form-select" aria-label="company" name="company">
                    <option value="">無</option>
                    @foreach($allCompanies as $allCompany)
                        <option
                            @if ($method === 'edit' && old('company', (isset($allCompany->company)?
                                                                                (($allCompany->company == $data->company) ? true : false)
                                                                                : false)))
                            selected
                            @endif
                            value="{{ $allCompany->id }}">{{ $allCompany->company }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-sm-4 mb-3 invoice_date">
                <label class="form-label">類別</label>
                <select class="form-select" aria-label="category" name="category">
                    <option value="">無</option>
                    @foreach($allCategories as $allCategory)
                        <option
                            @if ($method === 'edit' && old('category', (isset($allCategory->name)?
                                                                                (($allCategory->name == $data->category) ? true : false)
                                                                                : false)))
                            selected
                            @endif
                            value="{{ $allCategory->id }}">{{ $allCategory->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <x-b-form-group name="note_1" title="備註一" required="false">
                <input class="form-control @error('note_1') is-invalid @enderror"
                       name="note_1"
                       value="{{ old('note_1', (isset($data) ? ($data->note_1 ?? ''): '')) }}"/>
            </x-b-form-group>
            <x-b-form-group name="note_2" title="備註二" required="false">
                <input class="form-control @error('note_2') is-invalid @enderror"
                       name="note_2"
                       value="{{ old('note_2', (isset($data) ? ($data->note_2 ?? ''): '')) }}"/>
            </x-b-form-group>

            @error('name')
                <div class="alert alert-danger mt-3">{{ $message }}</div>
            @enderror

            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
            </div>
        </form>
    </div>

@endsection
