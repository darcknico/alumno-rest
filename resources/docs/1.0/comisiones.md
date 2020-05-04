# Comisiones

---

- [Inicio](#inicio)
- [Inscripción a comision](#inscripcion)
- [Asistencias](#asistencias)
- [Examenes](#examenes)
- [Horarios](#horarios)
- [Funciones](#funciones)
- [Reportes](#reportes)
- [Archivos](#archivos)

<a name="inicio"></a>
## Inicio

Las comision de alumnos, en donde se registran asistencias y examenes. Tambien indica el periodo lectivo que corresponde la inscripcion de un alumno.

### Formulario
- Año de cursada
- Identificacion de la comision
- Modalidad de cursada
- Materia que corresponde la comision

### Automatizacion de la Asistencia
- Inicio de clases
- Finalizacion de clases
- Activar automatizacion

### Responsable de la comision
- Usuario del sitema

### Docente a cargo de la cursada
- Listado de docentes

![Formulario de la comision](/imagenes/documentacion/formulario_comision.png)

> {info} La automatizacion de asistencia implica que en cada horario en el que se dicta, se genere la planilla de asistencia del corriente dia con todos sus alumnos sin estado(Asistio/Inasistencia/Justificado) por defecto..


<a name="inscripcion"></a>
## Inscripción a comision

Desde la parte de gestion de una inscripcion. Se da el siguiente formulario paso a paso.
- En el cual se indica primero el año a inscribir.
- Segundo listado de las comisiones disponibles del año elegido. Se aclara si posee una inscripcion previa en las comisiones de la materia.
- Tercero la confirmación de inscripcion a las comisiones seleccioandas.

![Paso 1](/imagenes/documentacion/formulario_comision_alumno_1.png)
![Paso 2](/imagenes/documentacion/formulario_comision_alumno_2.png)
![Paso 3](/imagenes/documentacion/formulario_comision_alumno_3.png)

<a name="asistencias"></a>
## Asistencias

Durante la gestion de una Comision. Se puede generar una planilla de asistencias con los estudiantes inscripto en la corriente comision.

- Fecha de la planilla: campo obligatorio

![Formulario de asistencia](/imagenes/documentacion/formulario_comision_asistencia.png)

<a name="examenes"></a>
## Examenes

Tambien generar los examenes durante la cursada de esa comision.

- Fecha del examen
- Nombre
- Tipo de examen: Parcial - Recuperatorio - Trabajo practico
- Observaciones

![Formulario del examen](/imagenes/documentacion/formulario_comision_examen.png)

<a name="horarios"></a>
## Horarios

Ademas de asignar horarios que la comision es dictada. Y asignar a este horario la capacidad de generar automaticamente la asistencia, si es que la comision lo permitio previamente.

- Nombre o descripcion del horario
- Asistencia automatica
- [Aula en el que es dictada](/documentacion/1.0/establecimiento#aulas)
- Dia de la semana
- Hora de apertura y cierre de la comision

![Formulario de un horario](/imagenes/documentacion/formulario_comision_horario.png)

> {info} Se clara que realiza la validacion previa del registro si no existe un horario que colicione con la hora de apertura y cierre, dentro de los horarios de la misma comision.

<a name="funciones"></a>
## Funciones

###Algoritmo para obtener el periodo lectivo

- Sea el año corriente
- Obtener comisiones inscriptas, donde el año dictado fuera menor estricto al año corriente.
- De estas inscripciones agarrar la comision en donde la materia sea del mayor periodo lectivo.
- Obtener las comisiones inscriptas, donde el año dictado sea coriente a este año
- De estas ultimas comisiones agarrar la comision en donde la materia sea del mayor periodo lectivo.
- Sea el periodo lectivo de la comision de años anteriores. sumar 1 si es menor o igual a 6.
- Comprar este periodo lectivo con la comision de este año, y si es mayor tomarlo como periodo lectivo.


<a name="reportes"></a>
## Reportes

<a name="archivos"></a>
## Archivos

![Inscripcion a comision](/imagenes/documentacion/comision_inscripcion.png)
