@component('mail::message')

@lang('email.specification.content')  

# @lang('email.specification.informationHeadline')  

@lang('email.specification.email') {{$user->email}}  
@lang('email.specification.name') {{$user->name}}  
@lang('email.specification.salutation') {{$user->salutation}}  
@lang('email.specification.user_company') {{$user->user_company}}  
@lang('email.specification.user_role') {{$user->user_role}}  
@lang('email.specification.user_url') {{$user->user_url}}  

@lang('email.specification.company') {{$user->company_name}}  
@lang('email.specification.website') {{$user->website}}  
@lang('email.specification.zipcode') {{$user->zipcode}}  
@lang('email.specification.location') {{$user->city}}  
@lang('email.specification.street') {{$user->street}}  
{{$user->additional_street_info}}  

@if(!empty($user->contact))
@lang('email.specification.contact') {{$user->contact}}  
@if(!empty($user->contact_function))
@lang('email.specification.contactFunction') {{$user->contact_function}}  
@endif
@endif
@if(!empty($user->phone))
@lang('email.specification.phone') {{$user->phone}}  
@endif
@if(!empty($user->partner_tracking))
@lang('email.specification.partnerTracking') {{$user->partner_tracking}}  
@endif

@include('email.signature')
@endcomponent

