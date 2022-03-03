@component('mail::message')
@lang('email.register.greeting', ['salutation' => $user->salutation, 'name' => $user->name])  

@lang('email.register.content', ['domain' => config('app.domain')])  

# @lang('email.register.features.headline')  
@lang('email.register.features.items', ['domain' => config('app.domain'), 'url' => config('app.url')])  

# @lang('email.register.tutorial.headline')
@component('mail::button', ['url' => $url])
    {{ __('Verify Email Address') }}
@endcomponent

@lang('email.register.contact', ['email' => config('app.contact.mail'), 'phone' => config('app.contact.phone')])  

@lang('email.ending')  
@include('email.signature')
@endcomponent
