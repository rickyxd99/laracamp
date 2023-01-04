@component('mail::message')
# Register Camp: {{$checkout->camp->title}}

Hi {{$checkout->user->name}}
<br>Thankyou for register on <b>{{$checkout->camp->title}}</b>,
please see payment instruction by click the button below!

@component('mail::button', ['url' => route('user.checkout.invoice', $checkout->id)])
Login Here
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent