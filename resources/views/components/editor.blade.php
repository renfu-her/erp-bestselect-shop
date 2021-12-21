<div class="cus-editor {{ $classes }}">
    <div id="{{ $id }}"></div>
</div>

@once
    @push('sub-styles')
    <style>
        
    </style>
    @endpush
    @push('sub-scripts')
    <script>
        let editor = Editor.createEditor('#' + @json($id), {
            height: @json($height),
            colorTool: @json($colorTool)
        });
    </script>
    @endpush
@endonce