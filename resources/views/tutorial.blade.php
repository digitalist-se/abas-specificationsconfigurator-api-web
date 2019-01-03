@extends('layout.default')

@php
    $loginURL = route('login');
    $contactMail = config('app.contact.mail', '');
    $contactPhone= config('app.contact.phone', '');
@endphp

@section('content')

    <div class="container tutorial content">
        <h1 class="headline">Hilfe / So funktioniert der ERP Planner</h1>
        <p class="copy">So hilft Ihnen der Lastenheft Generator bei der ERP-Auswahl:</p>
        <ul class="copy">
            <li>Loggen Sie sich unter <a href="{{$loginURL}}">{{$loginURL}}</a> ein.</li>
            <li>Der ERP Planner leitet Sie durch die verschiedenen Fragen, die zur Generierung Ihres Lastenheftes benötigt werden. Im Mittelpunkt stehen Ihre Anforderungen an das neue ERP-System.</li>
            <li>Dabei können Sie Ihren aktuellen Stand jederzeit speichern, die Beantwortung zu einem späteren Zeitpunkt fortsetzen oder das Lastenheft generieren.
                <br>Als Resultat erhalten Sie Ihr Lastenheft das Sie auch im Nachhinein noch editieren und ergänzen können.</li>
            <li>Von der Long List zur Short List: Ihr Lastenheft schicken Sie an in Frage kommende ERP-Anbieter und beginnen mit der Vorauswahl der potenziell geeigneten Lösungen.</li>
            <li>In Workshops mit den ERP-Anbietern betrachten Sie die Prozesse und Anforderungen aus dem Lastenheft detailliert.</li>
            <li>Alles passt und auch die Chemie stimmt? Dann folgt das Pflichtenheft, in dem der ERP-Anbieter darstellt, wie seine Lösung der Anforderungen aussieht.</li>
            <li>In Kombination mit dem Angebot stellt das Pflichtenheft die vertragliche Grundlage der zu erfüllenden Leistungen dar und ist die Basis für die ERP-Implementierung sowie die spätere Projektabnahme.</li>
        </ul>
        <p>Starten Sie jetzt mit dem ERP Planner und generieren Sie Ihr individuelles Lastenheft!<br>
            Noch Fragen? Wir helfen Ihnen gerne weiter!<br>
            Rufen Sie uns einfach an unter <a href="tel:{{$contactPhone}}">{{$contactPhone}}</a><br>
            oder schreiben Sie uns eine E-Mail an <a href="mailto:{{$contactMail}}">{{$contactMail}}</a>
    </div>
@endsection
