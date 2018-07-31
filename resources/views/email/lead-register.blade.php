@component('mail::message')
    @lang('email.lead.register.content')

    # @lang('email.lead.register.informationHeadline')
    
    @lang('email.lead.register.email') {{$user->email}}  
    @lang('email.lead.register.name') {{$user->name}}  

    @include('email.signature')
@endcomponent
