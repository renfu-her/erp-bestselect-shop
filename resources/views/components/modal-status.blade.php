<x-b-modal id="{{ $id }}">
    <x-slot name="title">物態說明</x-slot>
    <x-slot name="body">
       <!-- title content style-->
        @foreach ($orderStatus as $key => $value)
            <p class="{{ $value['style'] }} mb-0">
                {{ $value['title'] }}：{{ $value['content'] }}
            </p>
        @endforeach
    </x-slot>
</x-b-modal>
