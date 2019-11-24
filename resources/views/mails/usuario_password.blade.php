@component('mails.components.message')
# Notificación de cambio de contraseña

Hola {{ $usuario->apellido }}, {{ $usuario->nombre }}. 

Esta recibiendo este mensaje por que su contraseña fue reestablecida para el usuario {{ $usuario->email }}.

@if($password)
Por la siguiente contraseña: **{{ $password }}**
@endif

Saludos, y que estés bien !
@endcomponent