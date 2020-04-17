@component('mails.components.message')
# Hola {{ $alumno->apellido }}, {{ $alumno->nombre }}. 

Esta recibiendo este mensaje para notificarle que fue registrado una nueva inscripcion de mesa de examen.

- Mesa de examen: {{$mesa_examen_materia->mesa_examen->nombre}}
- Materia: {{$mesa_examen_materia->materia->nombre}}
@if ( $mesa_examen_materia->fecha )
- Fecha: {{ \Carbon\Carbon::parse($mesa_examen_materia->fecha)->format('d/m/Y') }}
@endif

Saludos, y que estés bien !
@endcomponent