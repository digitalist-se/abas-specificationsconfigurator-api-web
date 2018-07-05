@component('mail::message')
# @lang('email.specification.headline')

*@lang('email.specification.company')*
    {{$user->company_name}}<br>

{{$user->name}}<br>

*@lang('email.specification.phone')*
    {{$user->phone}}<br>
*@lang('email.specification.website')*
    {{$user->website}}<br>
*@lang('email.specification.address')*
    {{$user->street}}<br>
    {{$user->additional_street_info}}<br>
    {{$user->zipcode}} {{$user->city}}<br>
<br><br>
@if(!empty($user->contact))
*@lang('email.specification.contact')*
{{$user->salutation}} {{$user->contact}}<br>
*@lang('email.specification.contactFunction')*
    {{$user->contact_function}}<br>
@endif
@endcomponent
