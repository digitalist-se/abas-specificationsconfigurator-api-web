@component('mail::message')

@lang('email.specification.content')

*@lang('email.specification.informationHeadline')*

@lang('email.specification.email')
    {{$user->email}}<br>
@lang('email.specification.name')
{{$user->name}}<br>
@lang('email.specification.salutation')
{{$user->salutation}}<br>

@lang('email.specification.company')
    {{$user->company_name}}<br>
@lang('email.specification.website')
{{$user->website}}<br>
@lang('email.specification.zipcode')
{{$user->zipcode}}<br>
@lang('email.specification.location')
{{$user->city}}<br>
@lang('email.specification.street')
{{$user->street}}<br>
{{$user->additional_street_info}}<br>


@if(!empty($user->contact))
@lang('email.specification.contact')
{{$user->contact}}<br>
@if(!empty($user->contact_function))
@lang('email.specification.contactFunction')
{{$user->contact_function}}<br>
@endif
@endif
@if(!empty($user->phone))
    @lang('email.specification.phone')
    {{$user->phone}}<br>
@endif
@include('email.signature')
@endcomponent

