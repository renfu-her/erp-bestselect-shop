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
    {{-- <x-b-modal id="confirm-delete">
        <x-slot name="title">是否要刪除此人員？</x-slot>
        <x-slot name="body">123</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
        </x-slot>
    </x-b-modal> --}}

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
        </script>
    @endpush
@endOnce
