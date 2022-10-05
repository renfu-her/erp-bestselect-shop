@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">修改付款項目備註</h2>

    <form method="POST" action="{{ $form_action }}">
        @csrf
        <div class="card shadow p-4 mb-4">
            <div class="table-responsive tableOverBox">
                <table class="table table-sm table-hover tableList mb-1">
                    <thead class="small">
                        <tr>
                            <th scope="col" class="text-center">#</th>
                            <th scope="col">摘要說明</th>
                            <th scope="col" class="text-end">數量</th>
                            <th scope="col" class="text-end">金額</th>
                            <th scope="col">備註</th>
                            <th scope="col">付款項目備註</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $serial = 1;
                        @endphp

                        @foreach($item_list_data as $value)
                            <tr>
                                <td class="text-center">{{ $serial }}</td>
                                <td>{{ $value->title }}</td>
                                <td class="text-end">{{ $value->qty }}</td>
                                <td class="text-end">{{ number_format($value->total_price, 2) }}</td>
                                <td><input class="form-control form-control-sm -l" name="item[{{ $value->item_id }}][note]" type="text" value="{{ $value->note }}"></td>
                                <td><input class="form-control form-control-sm -l" name="item[{{ $value->item_id }}][po_note]" type="text" value="{{ $value->po_note }}"></td>
                            </tr>
                            @php
                                $serial++;
                            @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">確認</button>
            <a href="{{ url()->previous() }}" class="btn btn-outline-primary px-4" role="button">
                返回上一頁
            </a>
        </div>
    </form>
@endsection

@once
@push('sub-styles')
<style>

</style>
@endpush
@push('sub-scripts')
<script>

</script>
@endpush
@endonce