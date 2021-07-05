@extends('layout.default')

@php
    $contactMail = config('app.contact.mail', '');
    $contactPhone = config('app.contact.phone', '');
    $appURL = config('app.url', '');
@endphp

@section('content')
<div class="container content imprint">
    <h1 class="headline">Impressum</h1>
    <p><b>Angaben gemäß §5 TMG</b><br>
        abas Software GmbH<br>
        Gartenstraße 67<br>
        76135 Karlsruhe<br>
    </p>
    <p>
        <b>Vertreten durch:</b><br>
        Richard Furby & Paul Smolinski, Geschäftsführung
    </p>
    <p>Der Lastenheft-Generator ERP Planner ist ein Projekt abas Software GmbH.</p>
    <p>
        <b>Kontakt:</b><br>
        Telefon: {{$contactPhone}}<br>
        E-Mail: {{$contactMail}}<br>
        Internetadresse: <a href="{{$appURL}}">{{$appURL}}</a><br>
    </p>
    <p>
        <b>Registereintrag:</b><br>
        Eintragung im Handelsregister<br>
        Registernummer: HRB 734651<br>
        Registergericht: Amtsgericht Mannheim
    </p>
    <p><b>Haftungsausschluss:</b>
        Haftung für Inhalte
    </p>
    <p>Die Inhalte unserer Seiten wurden mit größter Sorgfalt erstellt. Für die Richtigkeit, Vollständigkeit und Aktualität der Inhalte können wir jedoch keine Gewähr übernehmen. Als Diensteanbieter sind wir gemäß § 7 Abs.1 TMG für eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich. Nach §§ 8 bis 10 TMG sind wir als Diensteanbieter jedoch nicht verpflichtet, übermittelte oder gespeicherte fremde Informationen zu überwachen oder nach Umständen zu forschen, die auf eine rechtswidrige Tätigkeit hinweisen. Verpflichtungen zur Entfernung oder Sperrung der Nutzung von Informationen nach den allgemeinen Gesetzen bleiben hiervon unberührt. Eine diesbezügliche Haftung ist jedoch erst ab dem Zeitpunkt der Kenntnis einer konkreten Rechtsverletzung möglich. Bei Bekanntwerden von entsprechenden Rechtsverletzungen werden wir diese Inhalte umgehend entfernen.</p>

    <p>Haftung für Links</p>
    <p>Unser Angebot enthält Links zu externen Webseiten Dritter, auf deren Inhalte wir keinen Einfluss haben. Deshalb können wir für diese fremden Inhalte auch keine Gewähr übernehmen. Für die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der Seiten verantwortlich. Die verlinkten Seiten wurden zum Zeitpunkt der Verlinkung auf mögliche Rechtsverstöße überprüft. Rechtswidrige Inhalte waren zum Zeitpunkt der Verlinkung nicht erkennbar. Eine permanente inhaltliche Kontrolle der verlinkten Seiten ist jedoch ohne konkrete Anhaltspunkte einer Rechtsverletzung nicht zumutbar. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Links umgehend entfernen.</p>
    <p>Urheberrecht</p>
    <p>Die durch die Seitenbetreiber erstellten bzw. verwendeten Inhalte und Werke auf diesen Seiten unterliegen dem deutschen Urheberrecht. Die Vervielfältigung, Bearbeitung, Verbreitung und jede Art der Verwertung außerhalb der Grenzen des Urheberrechtes bedürfen der Zustimmung des jeweiligen Autors bzw. Erstellers. Downloads und Kopien dieser Seite sind nur für den privaten, nicht kommerziellen Gebrauch gestattet. Soweit die Inhalte auf dieser Seite nicht vom Betreiber erstellt wurden, werden die Urheberrechte Dritter beachtet. Insbesondere werden Inhalte Dritter als solche gekennzeichnet. Sollten Sie trotzdem auf eine Urheberrechtsverletzung aufmerksam werden, bitten wir um einen entsprechenden Hinweis. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Inhalte umgehend entfernen.</p>
    <p>Quelle: <a href="https://www.muster-vorlagen.net/">Muster Vorlagen</a></p>
</div>
@endsection
