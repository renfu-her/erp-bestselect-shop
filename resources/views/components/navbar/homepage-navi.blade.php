{{-- Tabs Navbar --}}
<ul class="nav pm_navbar" role="tablist">
    <li class="nav-item">
        <a href="{{ Route('cms.homepage.navbar.index') }}"
            class="nav-link {{ isActive('navbar', $route_name) }}">導覽列Navbar</a>
    </li>
    <li class="nav-item">
        <a href="{{ Route('cms.homepage.banner.index') }}"
            class="nav-link {{ isActive('banner', $route_name) }}">橫幅廣告</a>
    </li>
    <li class="nav-item">
        <a href="{{ Route('cms.homepage.template.index') }}"
            class="nav-link {{ isActive('template', $route_name) }}">版型</a>
    </li>
</ul>
<hr class="narbarBottomLine mb-3">
