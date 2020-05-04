# Mesas de examenes

---

- [Inicio](#inicio)
- [Examen de la materia](#materias)
- [Inscripción del alumno](#inscripcion)
- [Docentes](#docentes)
- [Funciones](#funciones)
- [Reportes](#reportes)
- [Archivos](#archivos)

<a name="inicio"></a>
## Inicio

Las mesas de examenes son periodos en que una sede ofrece examenes finales a sus alumnos. Se debe aclarar sobre que materias van a tener examenes, quiens son docentes a cargo de esa mesa y los alumnos que estan inscriptos.

### Formulario

- Fecha de apertura de la mesa
- Fecha de cierre de la mesa
- Numero: autogenerado desde la sede
- Identificacion de la mesa

### Notificación a docnetes previo a la fecha

- Notificar por correo electronico. Esto generara un archivo que se adjuntara al correo.
- Notificar por aplicacion movil. Para aquellos docentes que tengan instalado la aplicacion movil.

![Formulario de la mesa de examen](/imagenes/documentacion/formulario_mesa.png)

<a name="materias"></a>
## Examen de la materia

Aqui se asignan las materias que tendran examenes en la mesa de examen previamente creada. Existen distintas formas de generar estos examenes y dejarlos habilitados para la inscripcion

### Forma simple

Se presenta un formulario con todos los campos que pueden ser editados de un examen.

- Materia a generar el examen
- Fecha y hora de realizacion
- Fecha de cierre de la acta de examen
- Ubicacion de realizacion
- Libro
- Folio libres
- Folio promocionales
- Folio regulares
- Observaciones

![Formulario de la mesa de examen por materia](/imagenes/documentacion/formulario_mesa_materia.png)

### Forma masiva

Una forma compleja pero rapida para generar todos los exames de manera masiva. Por defecto la fecha de realizacion del examen se asigna con la fecha de apertura de la mesas.

- Por filtro: de acuerdo al departamento y/o carrera. Generaran de acuerdo a las carreras que cumplan el filtro, usando las materias del plan de estudio que tiene como principal a la carrera.

- Por materia: muestra un listado completo de todas las materias de planes de estudios que esten como principal en las carreras.

![Generacion multiple por filtro](/imagenes/documentacion/formulario_mesa_materia_1.png)
![Generacion multiple por materia](/imagenes/documentacion/formulario_mesa_materia_2.png)

<a name="inscripcion"></a>
## Inscripción del alumno

Desde la parte de gestion de una inscripcion. Se da el siguiente formulario paso a paso.
- Primero, se indica primero el año y las mesas desponibles en ese año.
- Segundo, se muestra el listado de examenes disponibles para la mesa previamente seleccionada.
- Tercero la confirmación de inscripcion a las comisiones seleccioandas. Además de ver si el alumno posee saldo en su cuenta corriente, en el cual se vera reflejado en el acta de examen.

![Paso 1](/imagenes/documentacion/formulario_mesa_materia_alumno_1.png)
![Paso 2](/imagenes/documentacion/formulario_mesa_materia_alumno_2.png)
![Paso 3](/imagenes/documentacion/formulario_mesa_materia_alumno_3.png)

<a name="docentes"></a>
## Docentes

En la gestion del examen de una mesa. Esta habilitado la asociacion de doncentes, en el cual se vera reflejado en el acta de examen.

- Docente seleccionado
- Tipo de responsabilidad: Presidente - Vocal 1 - Vocal 2
- Observaciones

![Formulario de la mesa de examen por materia y docente](/imagenes/documentacion/formulario_mesa_materia_docente.png)

<a name="funciones"></a>
## Funciones

###Algoritmo para obtener el porcentaje aprobado de la carrera
- Obtener las mesas de exámenes finales, que pertenezcan a la inscripción, con la NOTA mayor o igual 4.
- Obtener las notas viejas, aquellas que pertenecen la inscripción, con la NOTA mayor o igual 4 y que las materias no estén en las mesas de examen final.
- Entonces la suma de la cantidad de exámenes finales y notas viejas, es la cantidad de materias aprobadas.
- Haciendo la regla de 3 simples se obtiene el porcentaje aprobado

###Algoritmo para obtener el promedio histórico con o sin desaprobados
- Obtener el promedio y cantidad exámenes finales, donde exista la nota final y pertenezca a la inscripción (Para excluir desaprobados, tomar nota final mayor a 4)
- Obtener el promedio y cantidad de notas viejas.  (Para excluir desaprobados, tomar nota final mayor a 4)
- Con los datos realizar la [media ponderada](https://es.wikipedia.org/wiki/Media_%28matem%C3%A1ticas%29#Media_aritm.C3.A9tica_ponderada) para obtener el promedio historico. 


<a name="reportes"></a>
## Reportes

<a name="archivos"></a>
## Archivos

![Inscripcion a mesa](/imagenes/documentacion/mesa_inscripcion.png)
