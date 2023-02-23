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
                <div class="alert alert-primary" role="alert">
                    <i class="bi bi-info-circle-fill"></i> 參數使用說明：（點選框中文字可自動複製）
                    <div class="d-flex flex-wrap" style="list-style-type: none;">
                        <div class="col-12 col-md-6">
                            <li><code class="border border-secondary">{$name}</code> - 消費者姓名</li>
                        </div>
                        <div class="col-12 col-md-6">
                            <li><code class="border border-secondary">{$email}</code> - 消費者Email</li>
                        </div>
                        <div class="col-12 col-md-6">
                            <li><code class="border border-secondary">{$sn}</code> - 發送優專券訂單編號</li>
                        </div>
                        <div class="col-12 col-md-6">
                            <li><code class="border border-secondary">{$title}</code> - 優惠券活動名稱</li>
                        </div>
                        <div class="col-12 col-md-6">
                            <li><code class="border border-secondary">{$c_title}</code> - 優惠類型</li>
                        </div>
                        <div class="col-12 col-md-6">
                            <li><code class="border border-secondary">{$d_value}</code> - 優惠內容</li>
                        </div>
                        <div class="col-12 col-lg-6">
                            <li><code class="border border-secondary">{$active_edate}</code> - 優惠券到期日</li>
                        </div>
                    </div>
                </div>
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

            $('.alert-primary code').on('click', function () {
                let range, selection;
                if (document.body.createTextRange) {
                    range = document.body.createTextRange();
                    range.moveToElementText(this);
                    range.select();
                } else if (window.getSelection) {
                    selection = window.getSelection();
                    range = document.createRange();
                    range.selectNodeContents(this);
                    selection.removeAllRanges();
                    selection.addRange(range);
                }
                copyToClipboard(range.toString());
            })
        </script>
    @endpush
@endOnce