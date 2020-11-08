@component('mail::message')
# Pago aprobado

### Detalles del pago:
- Monto total: ${{$pago->monto}}
- Descripción: {{$pago->obligacion->descripcion}}
@if($pago->observaciones)
- Observaciones: {{$pago->observaciones}}
@endif

Saludos.

@endcomponent
