{{-- Tabs Navbar --}}
<ul class="nav pm_navbar" role="tablist">
    <li class="nav-item">
        <a href="{{ Route('cms.homepage.index') }}"
            class="nav-link {{ isActive('index', $route_name) }}">導覽列Navbar</a>
    </li>
    <li class="nav-item">
        <a href="{{ Route('cms.homepage.index') }}"
            class="nav-link {{ isActive('edit', $route_name) }}">橫幅廣告</a>
    </li>
    <li class="nav-item">
        <a href="{{ Route('cms.homepage.index') }}"
            class="nav-link {{ isActive('edit', $route_name) }}">版型</a>
    </li>
</ul>
<hr class="narbarBottomLine mb-3">
