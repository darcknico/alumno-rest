@component('mail::message')
# Nueva preferencia de pago creada

### Detalles del pago:
- Monto total: ${{$pago->monto}}
- Descripción: {{$pago->obligacion->descripcion}}
@if($pago->observaciones)
- Observaciones: {{$pago->observaciones}}
@endif

Sigue el siguiente boton para completar tu pago:

@component('mail::button', ['url' => '{{$pago->preference_url}}'])
CONTINUAR CON MERCADO PAGO
@endcomponent

Si no funciona el boton, copia el siguiente enlace:
[{{$pago->preference_url}}]({{$pago->preference_url}})

Saludos.
@endcomponent
