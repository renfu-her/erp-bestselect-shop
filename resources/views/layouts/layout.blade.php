<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>
        @if(preg_match('/^admin((\/.*)|(-login$))/',
            Request::path()))
            喜鴻購物
        @else
            購物系統
        @endif
    </title>
    <link rel="stylesheet" href="{{ Asset('dist/css/app.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css">
    @stack('styles')

    {{-- icon --}}
    <link rel="icon" href="{{ Asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ Asset('favicon.ico') }}">
    <link rel="icon" type="image/png" href="{{ Asset('images/webicon/16.png') }}" sizes="16x16">
    <link rel="icon" type="image/png" href="{{ Asset('images/webicon/32.png') }}" sizes="32x32">
    <link rel="icon" type="image/png" href="{{ Asset('images/webicon/96.png') }}" sizes="96x96">
    <link rel="icon" type="image/png" href="{{ Asset('images/webicon/160.png') }}" sizes="160x160">
    <link rel="apple-touch-icon" href="{{ Asset('images/webicon/160.png') }}"/>
    <link rel="apple-touch-icon" href="{{ Asset('images/webicon/57.png') }}" sizes="57x57" />
    <link rel="apple-touch-icon" href="{{ Asset('images/webicon/60.png') }}" sizes="60x60">
    <link rel="apple-touch-icon" href="{{ Asset('images/webicon/72.png') }}" sizes="72x72" />
    <link rel="apple-touch-icon" href="{{ Asset('images/webicon/76.png') }}" sizes="76x76">
    <link rel="apple-touch-icon" href="{{ Asset('images/webicon/114.png') }}" sizes="114x114" />
    <link rel="apple-touch-icon" href="{{ Asset('images/webicon/120.png') }}" sizes="120x120">
    <link rel="apple-touch-icon" href="{{ Asset('images/webicon/144.png') }}" sizes="144x144" />    
    <link rel="apple-touch-icon" href="{{ Asset('images/webicon/152.png') }}" sizes="152x152">
    <meta name="msapplication-TileImage" content="{{ Asset('images/webicon/144.png') }}">
    <meta name="msapplication-square70x70logo" content="{{ Asset('images/webicon/70.png') }}">
    <meta name="msapplication-square150x150logo" content="{{ Asset('images/webicon/150.png') }}">
    <meta name="msapplication-wide310x150logo" content="{{ Asset('images/webicon/310x150.png') }}">
    <meta name="msapplication-square310x310logo" content="{{ Asset('images/webicon/310.png') }}"> 

</head>

<body>
    @yield('content')
    <script src="{{ Asset('dist/js/app.js') }}?2.0"></script>
    <script>
        $('#sidebarMenu').on('shown.bs.collapse', function () {
            console.log('show');
            sessionStorage.setItem('sidebar', 'show');
            const activeNav = $('#sidebarMenu .btn-toggle-nav li.active');
            if (activeNav.length > 0) {
                $('#sidebarMenu').animate({
                    scrollTop: activeNav.closest('li.border-top').offset().top - 63
                }, 500);
            }
        }).on('hide.bs.collapse', function () {
            console.log('hide');
            sessionStorage.setItem('sidebar', 'hide');
            $('#sidebarMenu').scrollTop(0);
        });
        if (window.innerWidth >= 576 && sessionStorage.sidebar === 'show') {
            $('#sidebarMenu').addClass('show');
        } else {
            $('#sidebarMenu').removeClass('show');
            sessionStorage.setItem('sidebar', 'hide');
        }
    </script>
    @stack('scripts')
</body>

</html>
