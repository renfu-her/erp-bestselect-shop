@extends('layouts.main')

@section('sub-content')
    <h2 class="mb-4">編輯優惠劵到期通知信</h2>

    <form id="form1" action="{{ $form_action }}" method="post">
        @csrf
        <div class="card shadow p-4 mb-4">
            <div class="col-12 mb-3">
                <label class="form-label">主旨 <span class="text-danger">*</span></label>
                <input class="form-control @error('mail_subject') is-invalid @enderror" name="mail_subject" type="text" placeholder="到期通知信主旨" maxlength="255" value="{{ old('mail_subject', $customer_coupon->mail_subject) }}" aria-label="到期通知信主旨" required>
                @error('mail_subject')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 mb-3">
                <label class="form-label">內容 <span class="text-danger">*</span></label>
                <textarea id="editor" name="mail_content" hidden></textarea>
            </div>
        </div>

        <div class="col-auto">
            <input type="hidden" name="back_url" value="{{ old('back_url', $back_url) }}">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
            <a href="{{ old('back_url', $back_url) }}" class="btn btn-outline-primary px-4" role="button">取消</a>
        </div>
    </form>
@endsection

@once
    @push('sub-scripts')
        <script src="{{ Asset("plug-in/tinymce/tinymce.min.js") }}"></script>
        <script src="{{ Asset("plug-in/tinymce/myTinymce.js") }}"></script>
        <script>
            let mail_content = @json($customer_coupon->mail_content);
            mail_content = mail_content ? mail_content : '';

            tinymce.init({
                selector: '#editor',
                auto_focus: 'editor',
                ...TINY_OPTION
            }).then((editors) => {
                editors[0].setContent(mail_content);
            });

            $('#form1').submit(function(e) {
                $('textarea[name="mail_content"]').val(tinymce.get('editor').getContent());
            });
        </script>
    @endpush
@endOnce