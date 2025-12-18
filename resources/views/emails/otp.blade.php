@component('mail::message')
# Kode OTP Verifikasi

Berikut adalah kode OTP untuk verifikasi akun Anda:

@component('mail::panel')
**{{ $otpCode }}**
@endcomponent

Kode ini berlaku selama 10 menit. Jangan bagikan kode ini kepada siapa pun.

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent


