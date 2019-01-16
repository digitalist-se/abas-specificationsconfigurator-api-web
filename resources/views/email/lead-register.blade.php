@component('mail::message')
    @lang('email.lead.register.content')

    # @lang('email.lead.register.informationHeadline')
    
    @lang('email.lead.register.email') {{$user->email}}  
    @lang('email.lead.register.name') {{$user->name}}
    @lang('email.lead.register.userCompany') {{$user->user_company}}
    @lang('email.lead.register.userRole') {{$user->user_role}}
    @lang('email.lead.register.userUrl') {{$user->user_url}}
    @if (!empty($user->partner_tracking))@lang('email.lead.register.partnerTracking') {{$user->partner_tracking}}
    @endif
    
    @include('email.signature')
@endcomponent
