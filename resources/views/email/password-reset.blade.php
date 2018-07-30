@component('mail::message')
@lang('email.password.reset.greeting', ['salutation' => $user->salutation, 'name' => $user->name])  

@lang('email.password.reset.content', ['domain' => config('app.domain')])  

**@lang('email.password.reset.description')**  

@component('mail::button', ['url' => $action])
    @lang('email.password.reset.action')
@endcomponent

# @lang('email.password.reset.generate-now')  

[{{config('app.domain')}}]({{config('app.url')}})

@lang('email.register.contact', ['email' => config('app.contact.mail'), 'phone' => config('app.contact.phone')])  

@lang('email.ending')  
@include('email.signature')
@endcomponent
