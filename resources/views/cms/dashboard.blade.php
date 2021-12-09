@extends('layouts.main')
@section('sub-content')

    <a href="/demo" class="btn btn-warning">Demo page</a>
    
@endsection
@once
    @push('sub-styles')
    <style>
    </style>
    @endpush
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
