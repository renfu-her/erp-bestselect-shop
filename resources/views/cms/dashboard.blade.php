@extends('layouts.main')
@section('sub-content')

    <button type="button" class="btn btn-primary" title="sasa" data-toggle="tooltip" id="liveToastBtn">
        Show live toast</button>


    <!-- Button trigger modal -->
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
        Launch demo modal
    </button>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <a href="#" data-href="#" data-bs-toggle="modal" data-bs-target="#confirm-delete" type="button" class="btn btn-danger btn-sm">
        123
    </a>
    <x-b-modal id="confirm-delete">
        <x-slot name="title">是否要刪除此人員？</x-slot>
        <x-slot name="body">123</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
        </x-slot>
    </x-b-modal>
    <div calss="form-group">
        <label class="col-form-label">
            地址
            <span class="text-danger">*</span>
        </label>
        <div class="input-group has-validation">
            <select class="form-select @error('city_id') is-invalid @enderror" style="max-width:20%" id="city_id" name="city_id">
                <option>請選擇</option>
                @foreach ($citys as $city)
                    <option value="{{ $city['city_id'] }}" >{{ $city['city_title'] }}</option>
                @endforeach
            </select>
            <select class="form-select @error('region_id') is-invalid @enderror" style="max-width:20%" id="region_id" name="region_id">
                <option>請選擇</option>
                @foreach ($regions as $region)
                    <option value="{{ $region['region_id'] }}" >{{ $region['region_title'] }}</option>
                @endforeach
            </select>
            <input name="addr" type="text" class="form-control @error('addr') is-invalid @enderror"
                value="">
            <button class="btn btn-outline-success" type="button" id="format_btn">格式化</button>
          
        </div>
    </div>

@endsection
@once
    @push('sub-scripts')
        <script>
            var toastTrigger = $('#liveToastBtn')
            if (toastTrigger) {
                toastTrigger.on('click', function() {
                    toast.show('測試測試測試測試測試測試測試測試測試', { title: '錯誤錯誤!', type: 'danger' });
                });
            }

            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });

            let cityElem = $('#city_id');
            let regionElem = $('#region_id')
            let addrInputElem = $('input[name=addr]');

            cityElem.on('change', function(e) {
                getRegionsAction($(this).val());
            });

            function getRegionsAction(city_id, region_id) {
                Addr.getRegions(city_id)
                    .then(re => {
                        Elem.renderSelect(regionElem, re.datas, {
                            default: region_id,
                            key: 'region_id',
                            value: 'region_title'
                        });
                    });
            }

            $('#format_btn').on('click', function(e) {
                let addr = addrInputElem.val();

                if (addr) {
                    Addr.addrFormating(addr).then(re => {
                        addrInputElem.val(re.data.addr);
                        if (re.data.city_id) {
                            cityElem.val(re.data.city_id);
                            getRegionsAction(re.data.city_id, re.data.region_id);

                        }
                    });
                }
            });
        </script>
    @endpush
@endOnce
