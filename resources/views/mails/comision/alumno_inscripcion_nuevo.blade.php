@component('mails.components.message')
# Hola {{ $alumno->apellido }}, {{ $alumno->nombre }}. 

Esta recibiendo este mensaje para notificarle que fue registrado una nueva inscripcion de comision.

- Materia: {{$comision->materia->nombre}}
- Año: {{$comision->anio}}
- Numero: {{$comision->numero}}
@if ( $comision->clase_inicio )
- Fecha de inicio: {{ \Carbon\Carbon::parse($comision->clase_inicio)->format('d/m/Y') }}
@endif

Saludos, y que estés bien !
@endcomponent