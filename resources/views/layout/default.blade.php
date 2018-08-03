<!DOCTYPE html>
<html>
    <head>
        <title>@yield('title', @config('app.name'))</title>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="stylesheet" href="{{ mix('/css/vendor.css') }}" type="text/css" >
        <link rel="stylesheet" href="{{ mix('/css/app.css') }}" type="text/css" >

        <link rel="apple-touch-icon" sizes="57x57" href="{{asset('favicons/apple-touch-icon-57x57.png')}}">
        <link rel="apple-touch-icon" sizes="60x60" href="{{asset('favicons/apple-touch-icon-60x60.png')}}">
        <link rel="apple-touch-icon" sizes="72x72" href="{{asset('favicons/apple-touch-icon-72x72.png')}}">
        <link rel="apple-touch-icon" sizes="76x76" href="{{asset('favicons/apple-touch-icon-76x76.png')}}">
        <link rel="apple-touch-icon" sizes="114x114" href="{{asset('favicons/apple-touch-icon-114x114.png')}}">
        <link rel="apple-touch-icon" sizes="120x120" href="{{asset('favicons/apple-touch-icon-120x120.png')}}">
        <link rel="apple-touch-icon" sizes="144x144" href="{{asset('favicons/apple-touch-icon-144x144.png')}}">
        <link rel="apple-touch-icon" sizes="152x152" href="{{asset('favicons/apple-touch-icon-152x152.png')}}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{asset('favicons/apple-touch-icon-180x180.png')}}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{asset('favicons/favicon-32x32.png')}}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{asset('favicons/favicon-16x16.png')}}">
        <link rel="manifest" href="{{asset('favicons/site.webmanifest')}}">
        <link rel="mask-icon" href="{{asset('favicons/safari-pinned-tab.svg')}}" color="#008bd0">
        <link rel="shortcut icon" href="{{asset('favicons/favicon.ico')}}">
        <meta name="msapplication-TileColor" content="#000000">
        <meta name="msapplication-TileImage" content="{{asset('favicons/mstile-144x144.png')}}">
        <meta name="msapplication-config" content="{{asset('favicons/browserconfig.xml')}}">
        <meta name="theme-color" content="#008bd0">

    </head>
    <body>
        <header class="toolbar">
            <div class="toolbar-container">
                <a href="{{route('landingpage')}}">
                    <img class="logo" src="{{asset('images/logo_white.png')}}" >
                </a>
                <span class="content-spacer"></span>
                <a class="action" href="{{route('register')}}" title="@lang('navigation.register')">
                    <span class="icon"><img src="{{asset('images/login.svg')}}" title="@lang('navigation.register')"> </span>
                    <span class="text">
                        @lang('navigation.register')
                    </span>
                </a>
                <a class="action" href="{{route('login')}}" title="@lang('navigation.login')">
                    <span class="icon"><img src="{{asset('images/login.svg')}}" title="@lang('navigation.login')"> </span>
                    <span class="text">
                        @lang('navigation.login')
                    </span>
                </a>
            </div>
        </header>

        <div id="main-content">
            @yield('content')
        </div>


        <footer class="footer">
            <div class="footer-container">
                <span class="navigation">
                  <a class="action" href="{{route('tutorial')}}">
                      @lang('navigation.tutorial')
                  </a>
                  <a class="action" href="{{route('imprint')}}">
                      @lang('navigation.imprint')
                  </a>
                  <a class="action" href="{{route('data-privacy')}}">
                    @lang('navigation.data-privacy')
                  </a>
                </span>
            </div>
        </footer>
        <script src="{{ mix('/js/app.js') }}"></script>
    </body>
</html>
