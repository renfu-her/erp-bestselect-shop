<div class="toast-container position-fixed bottom-0 right-0 p-3" style="z-index: 1065; right: 0; bottom: 0;">
    {{-- 偵測 flash session 自動產生 --}}
    @if (!is_null($data))
        <div id="autoToast" class="toast hide toast-{{ $data->type }}" role="alert" aria-live="assertive"
            data-bs-delay="{{ $data->delay }}" aria-atomic="true">
            @if (isset($data->title))
                <div class="toast-header">
                    <div class="rounded-3 me-2" style="width:20px;height:20px">
                        @if ($data->type == 'danger')
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        @else
                            <i class="bi bi-info-circle-fill"></i>
                        @endif
                    </div>
                    <strong class="me-auto t-title">{{ $data->title }}</strong>
                    @if (isset($data->subTitle))
                        <small class="t-subTitle"></small>
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body t-content">
                    @if (isset($data->content))
                        {{ $data->content }}
                    @endif
                </div>
            @else
                <div class="d-flex align-items-center">
                    <div class="toast-body t-content flex-grow-1">
                        @if (isset($data->content))
                            {{ $data->content }}
                        @endif
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            @endif
        </div>
    @endif

    {{-- JS 呼叫 --}}
    {{-- 無標題 --}}
    <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex align-items-center">
            <div class="toast-body t-content flex-grow-1"></div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    {{-- 有標題 --}}
    <div id="liveToast_h" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <div class="rounded-3 me-1" style="width:20px;height:20px">
                <i class="bi"></i>
            </div>
            <strong class="me-auto t-title"></strong>
            <small class="t-subTitle"></small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body t-content"></div>
    </div>
</div>
