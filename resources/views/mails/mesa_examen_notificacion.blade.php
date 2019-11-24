@component('mails.components.message')
# Notificación de mesas de examen proxima

Hola {{ $docente->usuario->apellido }}, {{ $docente->usuario->nombre }}. 

Esta recibiendo este mensaje para notificarle que esta por comenzar la mesa de examen de la matereria **{{ $mesa_examen_materia->materia->nombre }}**. Realizada en el siguiente establecimiento **{{ $sede->nombre }}**.

Saludos, y que estés bien !
@endcomponent