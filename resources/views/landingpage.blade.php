@extends('layout.default')

@php
    $contactMail = config('app.contact.mail', '');
    $contactPhone= config('app.contact.phone', '');
@endphp

@section('content')
    <section class="hero">
        <img src="{{asset('images/hero.jpg')}}" />
        <div class="container">
            <div class="headlines">
                <h1 class="headline">@lang('landingpage.headline')</h1>
                    <h2 class="subline">@lang('landingpage.subline')</h2>
            </div>
        </div>
    </section>
    <section class="content intro">
        <div class="container">
            <p class="headline">@lang('landingpage.intro.headline')</p>
            <p class="subline">@lang('landingpage.intro.subline')</p>
            <p class="copy">@lang('landingpage.intro.copy')</p>
            <p><a href="{{route('tutorial')}}" class="button">@lang('landingpage.intro.tutorial')</a></p>
            <p><a href="{{route('register')}}" class="button">@lang('landingpage.intro.register')</a></p>
            <p><a href="{{route('login')}}">@lang('landingpage.intro.login')</a></p>
        </div>
    </section>
    <section class="content contact">
        <div class="container">
            <p class="headline">
                Noch Fragen? Wir helfen Ihnen gerne weiter!
            </p>
            <p class="copy">
                Der Lastenheft-Generator ERP Planner ist ein gemeinsames Projekt der Evolvio GmbH und des ERP-Spezialisten abas Software AG. <br>
                Rufen Sie uns einfach an unter <a href="tel:{{$contactPhone}}">{{$contactPhone}}</a><br>
                oder schreiben Sie uns eine E-Mail an <a href="mailto:{{$contactMail}}">{{$contactMail}}</a>
            </p>
        </div>
    </section>
    <section class="content features">
        <div class="container">
            <p class="headline">@lang('landingpage.features.headline')</p>
            <p class="copy">@lang('landingpage.features.copy')</p>
            <p class="subline">Ihre Vorteile mit dem Lastenheft-Generator ERP Planner:</p>
            <div class="tiles">
                <div class="tile">
                    <img class="icon" src="{{asset('images/tile-erp.svg')}}" title="@lang('landingpage.features.tiles.erp')">
                    <span class="text">
                        @lang('landingpage.features.tiles.erp')
                    </span>
                </div>
                <div class="space-helper" aria-hidden="true"></div>
                <div class="tile">
                    <img class="icon" src="{{asset('images/tile-tempo.svg')}}" title="@lang('landingpage.features.tiles.tempo')">
                    <span class="text">
                        @lang('landingpage.features.tiles.tempo')
                    </span>
                </div>
                <div class="space-helper vertical" aria-hidden="true"></div>
                <div class="tile">
                    <img class="icon" src="{{asset('images/tile-ohne-berater.svg')}}" title="@lang('landingpage.features.tiles.ohne-berater')">
                    <span class="text">
                        @lang('landingpage.features.tiles.ohne-berater')
                    </span>
                </div>
                <div class="space-helper" aria-hidden="true"></div>
                <div class="tile">
                    <img class="icon" src="{{asset('images/tile-cockpit.svg')}}" title="@lang('navigation.cockpit')">
                    <span class="text">
                        @lang('landingpage.features.tiles.cockpit')
                    </span>
                </div>
            </div>
        </div>
    </section>
    <section class="slider">
        <div class="container nav-arrows"></div>
        <div class="slide">
            <div class="container">
                <div class="image">
                    <img src="{{asset('images/slide-1-kategorie-uebersicht.jpg')}}" alt="" title="" height="600px" />
                </div>
                <div class="text">
                    <p class="headline">@lang('landingpage.slider.slide1.headline')</p>
                    <p class="copy">@lang('landingpage.slider.slide1.text')</p>
                </div>
            </div>
        </div>
        <div class="slide">
            <div class="container">
                <div class="image">
                    <img src="{{asset('images/slide-2-ampel.jpg')}}" alt="" title="" height="600px" />
                </div>
                <div class="text">
                    <p class="headline">@lang('landingpage.slider.slide2.headline')</p>
                    <p class="copy">@lang('landingpage.slider.slide2.text')</p>
                </div>
            </div>
        </div>
        <div class="slide">
            <div class="container">
                <div class="image">
                    <img src="{{asset('images/slide-3-frageseite.jpg')}}" alt="" title="" height="600px" />
                </div>
                <div class="text">
                    <p class="headline">@lang('landingpage.slider.slide3.headline')</p>
                    <p class="copy">@lang('landingpage.slider.slide3.text')</p>
                </div>
            </div>
        </div>
        <div class="slide">
            <div class="container">
                <div class="image">
                    <img src="{{asset('images/slide-4-screen-mockup-cockpit.png')}}" alt="" title="" height="600px" />
                </div>
                <div class="text">
                    <p class="headline">@lang('landingpage.slider.slide4.headline')</p>
                    <p class="copy">@lang('landingpage.slider.slide4.text')</p>
                </div>
            </div>
        </div>
    </section>
@endsection
