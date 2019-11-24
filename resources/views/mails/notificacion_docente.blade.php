@component('mails.components.message')
# Notificación de mesas de examen

Hola {{ $docente->usuario->apellido }}, {{ $docente->usuario->nombre }}. 

Esta recibiendo este mensaje con un archivo adjunto de las mesas de exámenes que esta asociado. Realizada(s) en el siguiente establecimiento **{{ $sede->nombre }}** en el periodo **{{ $periodo }}**.

Saludos, y que estés bien !
@endcomponent