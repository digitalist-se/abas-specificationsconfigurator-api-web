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
