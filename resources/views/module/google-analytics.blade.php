@if( config('app.google-analytics-id') )
    <script async src="https://www.googletagmanager.com/gtag/js?id={{config('app.google-analytics-id')}}"></script>
    <script>

        var gaProperty = '{{config('app.google-analytics-id')}}';
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', gaProperty, { 'anonymize_ip': true });

        var disableStr = 'ga-disable-' + gaProperty;
        if (document.cookie.indexOf(disableStr + '=true') > -1) {
            window[disableStr] = true;
        } else {
            var script = document.createElement('script');
            script.setAttribute('type', 'text/javascript');
            script.setAttribute('src', 'https://www.googletagmanager.com/gtag/js?id='+gaProperty);
            document.body.appendChild(script);
        }
        function gaOptout() {
            document.cookie = disableStr + '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; domain={{config('app.domain')}}; path=/';
            window[disableStr] = true;
        }
    </script>
@endif
