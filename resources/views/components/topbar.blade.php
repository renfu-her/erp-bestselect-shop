<header class="navbar navbar-dark sticky-top flex-md-nowrap p-0 {{ $userType }}">
    <div class="w-100 d-flex align-items-center">
        <!-- 漢堡清單鈕 toggler -->
        <div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="true"
                aria-label="Toggle navigation">
                <i class="bi bi-list"></i>
            </button>
        </div>
        <!-- logo -->
        <div class="mainLogo d-flex align-items-center justify-content-center flex-grow-1 flex-sm-grow-0">
            <a href="{{ route('cms.dashboard') }}" class="h-100">
                <img src="{{ Asset('images/Best-logo-white.png') }}" alt="喜鴻國際">
            </a>
        </div>
        <!-- 站名 brand -->
        <div class="navbar-brand d-none d-sm-block m-0 d-flex flex-grow-1" aria-label="購物系統"></div>
        <!-- 功能鈕 iconBtn -->
        <div class="navbar-iconBtn d-flex justify-content-end">
            <!-- *隱藏按鈕請加 hidden -->
            <div hidden>
                <button type="button" class="btn rounded-circle text-white fs-5 icon-btn">
                    <i class="bi bi-bell-fill"></i>
                </button>
            </div>
            <!-- 分隔線 -->
            <div class="divider"></div>
        </div>
        <!-- 會員 member -->
        <div class="px-3 navbar-member">
            <div class="dropdown">
                <a id="memberMenu" class="btn dropdown-toggle text-white pe-sm-2 d-flex align-items-center" href="#"
                    role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div id="memberAvatar" class="rounded-circle d-inline-block"></div>
                    <span class="d-none d-md-inline">歡迎，<span id="memberName">{{ $name }}</span></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="memberMenu">
                    <li>
                        <a class="dropdown-item" href="{{ route('cms.usermnt.edit') }}">
                            <i class="bi bi-person me-1"></i>資料維護
                        </a>
                    </li>
                    @if(is_null($customer))
                        <li>
                            <a class="dropdown-item" href="{{ route('cms.usermnt.customer-binding') }}">
                                <i class="bi bi-link-45deg me-1"></i>會員綁定
                            </a>
                        </li>
                    @else
                        <li>
                            <a class="dropdown-item" href="{{ Route('cms.customer.order', ['id' => $customer->id], true) }}">
                                <i class="bi bi-person-rolodex me-1"></i>會員資料
                            </a>
                        </li>
                    @endif
                    <li>
                        <a class="dropdown-item" href="{{ $url }}" target="_blank">
                            <i class="bi bi-bag me-1"></i>喜鴻購物<i class="bi bi-box-arrow-up-right ms-1 text-black-50" style="font-size: 8px"></i>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ $issueUrl }}" target="_blank">
                            <i class="bi bi-flag me-1"></i>錯誤回報系統<i class="bi bi-box-arrow-up-right ms-1 text-black-50" style="font-size: 8px"></i>
                        </a>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('cms.logout') }}">
                            <i class="bi bi-box-arrow-left me-1"></i>登出
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
