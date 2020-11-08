# El establecimiento

---

- [Sedes](#sedes)
- [Departamentos](#departamentos)
- [Aulas](#aulas)
- [Modalidades](#modalidades)
- [Becas](#becas)
- [Tipos de abandono](#tipos_abandono)

<a name="sedes"></a>
## Sedes

Una sede viene con datos precargados a la hora de su registro, como la sala del CHAT. Lleva la cuenta de el numero de pagos, cantidad de mesas de examen.
Su formulario tiene como campos de ingreso:
- Nombre: maximo de 191 caracteres, obligatorio
- Ubicacion: maximo de 191 caracteres y opcional
- Localidad: simil al anterior
- Codigo Postal
- Direccion: maximo de 191 caracteres y opcional
- Telefono
- Celular
- Correo electronico
- Punto de venta: al generarse un pago se le adjunta por defecto el 2 si no tiene punto de venta la sede.

La mayoria de los campos son usados en la notificación por medio del correo electronico. Como son: al registro de un alumno, la inscripcion a una carrera, inscripcion a comision, inscripcion a mesa de examen.
También son usados en la generacion de distintos reportes a lo largo del sistema.

![image](/imagenes/documentacion/formulario_sede.png)

<a name="departamentos"></a>
## Departamentos

Las carreras pertenecen a un departamento en especifico. Sus campos son:
- Nombre: maximo de 191 caracteres, obligatorio

![image](/imagenes/documentacion/formulario_departamento.png)

<a name="aulas"></a>
## Aulas

Las aulas pertenecientes a una sede. Son usadas para asignar comisiones y mesas de examenes.
- Numero: numero mayor a 0 (cero) obligatorio
- Capacidad Maxima: numero mayor a 0 (cero) obligatorio
- Nombre: maximo de 191 caracteres

![image](/imagenes/documentacion/formulario_aula.png)

<a name="modalidades"></a>
## Modalidades
La modalidad es la forma en que un plan de estudio tiene disponible su incripcion y las comisiones a dictar.
- Nombre: maximo de 191 caracteres, obligatorio
- Descripcion: maximo de 191 caracteres

![image](/imagenes/documentacion/formulario_modalidad.png)

<a name="becas"></a>
## Becas
En la inscripcion de un alumno se le debe asignar o no una beca, asi este sera usado en la generación del plan de pagos
Su formulario se compone de los siguientes campos:
- Nombre: maximo de 191 caracteres, obligatorio
- Descuento a Cuota del descuento: decimal, obligatorio, valor entre 0 y 100
- Descuento a Matricula: decimal, obligatorio, valor entre 0 y 100
- Descripcion: maximo de 191 caracteres

![image](/imagenes/documentacion/formulario_beca.png)

<a name="tipos_abandono"></a>
## Tipos de abandono
Listado de formas u opciones en el cual un alumno puede darse su abandono en la carrera.
- Nombre: maximo de 191 caracteres, obligatorio
- Descripcion: maximo de 191 caracteres

![image](/imagenes/documentacion/formulario_tipo_abandono.png)
