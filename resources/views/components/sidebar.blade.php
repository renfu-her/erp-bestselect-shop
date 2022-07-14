<nav id="sidebarMenu" class="col-12 col-sm-3 p-0 sidebar collapse">
    <ul class="list-unstyled ps-0">
        @foreach ($tree as $unit)
            <!-- 第一層 -->

            <li class="mb-1">
                <div class="btn btn-toggle collapsed" data-bs-toggle="collapse"
                    data-bs-target="#collapse-{{ $unit['menu_id'] }}" aria-expanded="true">
                    <i class="bi {{ $unit['icon'] ?? 'bi-box' }}"></i>{{ $unit['title'] }}
                </div>
                <!-- 第二層 -->
                @if (isset($unit['child']))
                    <div class="collapse show" id="collapse-{{ $unit['menu_id'] }}">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1">
                            @foreach ($unit['child'] as $unit2)
                                <li class="{{ isActive($unit2['controller_name'], $controllerName) }}">
                                    <a href="{{ Route($unit2['route_name']) }}"
                                        class="link-dark nav-link">{{ $unit2['title'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </li>

            <!-- <li class="border-top my-3"></li> -->
        @endforeach

    </ul>
</nav>
