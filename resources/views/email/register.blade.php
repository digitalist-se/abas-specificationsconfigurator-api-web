@component('mail::message')
@lang('email.register.greeting', ['salutation' => $user->salutation, 'name' => $user->name])  

@lang('email.register.content')


@lang('email.register.verify')

@component('mail::button', ['url' => $url])
    {{ __('email.register.verifyButton') }}
@endcomponent

@lang('email.register.verifyLink', ['url' => $url])


@lang('email.register.ignore')


@lang('email.ending')

@include('email.signature')
@endcomponent
