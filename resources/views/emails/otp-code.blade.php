@component('mail::message')
# OTP Verification Mail
 Your otp code is : {{ $details['code'] }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
