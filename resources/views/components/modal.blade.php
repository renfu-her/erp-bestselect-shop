<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label"
    aria-hidden="true">
    <div class="modal-dialog {{ $size }}">
        <div class="modal-content">
            @if (isset($title))
                <div class="modal-header">
                    <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            @endif
            @if (isset($body))
                <div class="modal-body">
                    {{ $body }}
                </div>
            @endif
            @if (isset($foot))
                <div class="modal-footer">
                    @if ($cancelBtn === 'TRUE')
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    @endif
                    {{ $foot }}
                </div>
            @endif
        </div>
    </div>
</div>
