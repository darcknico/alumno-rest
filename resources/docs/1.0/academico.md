# El academia

---

- [Alumnos](#alumnos)
- [Carreras](#carreras)
- [Planes de Estudio](#planes_estudio)
- [Materias](#materias)
- [Inscripciones](#inscripciones)
- [Planes de Pago](#planes_pago)
- [Pagos](#pagos)

<a name="alumnos"></a>
## Alumnos

Al ingresar el documento el sistema valida si existe tal alumno en las sedes asociadas. Si es asi el alumno es exportado, y la informacion escencial es compartida (feciah del alumno, inscripciones realizadas). En caso contrario un nuevo alumno es registrado en el sistema.

Su formulario tiene como campos de ingreso:
### Datos personales
- Tipo de Documento: CUIT - CUIL - LE - LC - DNI - Otros
- Numero de documento
- Nombre del alumno: maximo de 191 caracteres, obligatorio
- Apellido del alumno: maximo de 191 caracteres, obligatorio
- Fecha de nacimiento
- Lugar de nacimiento
- Nacionalidad
- Sexo
- Estado civil: Soltero/a - Comprometido/a - Casado/a - Divorciado/a - Viudo/a

### Información de contacto
- Correo electronico
- Telefono
- Celular

### Residencia
- Provincia
- Localidad
- Codigo postal
- Domicilio
- Numero
- Departamento
- Piso

### Datos adicionales

- Observaciones

### Documentación presentada

- Tipo de documentacion:
	- Foto 4x4
	- Fotocopia DNI
	- Titulo Universitario
	- Titulo Secundario
	- Certificado de domicilio
	- Certificado de buena salud, grupo sanguineo y factor rh
	- Otro
	- Certificado de Nacimiento
- Observaciones
- Archivo adjunto

![Formulario del alumno](/imagenes/documentacion/formulario_alumno.png)

Lo siguiente es el formulario para registrar una documentacion presentada.

![Formulario de documentacion presentada](/imagenes/documentacion/formulario_alumno_1.png)

<a name="carreras"></a>
## Carreras

Las carreras pertenecen a un departamento en especifico. Sus campos son:
- [Departamento](/documentacion/1.0/establecimiento#departamentos) que pertenece la carrera
- Nombre: maximo de 191 caracteres, obligatorio
- Nombre acortado: maximo de 191 caracteres, obligatorio
- Titulo
- Descripcion
- [Modalidades disponibles](/documentacion/1.0/establecimiento#modalidades)

![image](/imagenes/documentacion/formulario_carrera.png)

<a name="planes_estudio"></a>
## Planes de Estudio

Continuando, el plan de estudio pertenece a una carrera.
- Nombre: maximo de 191 caracteres, obligatorio
- Codigo: obligatorio
- Año del plan
- Horas empleadas a cursar (autocalculable)
- Numero de resolución

![image](/imagenes/documentacion/formulario_plan_estudio.png)

<a name="materias"></a>
## Materias

Terminando una carrera, se deben registrar las materias que pertenecen al plan de estudio.
- Nombre: maximo de 191 caracteres, obligatorio
- Codigo de identificacion
- Horas a cursar
- Regimen de cursada: Cuatrimestral - Anual
- Periodo lectivo: disponibles desde primer hasta quinto año

![image](/imagenes/documentacion/formulario_materia.png)

> {info} Una ves registrado la materia, se puede indicar la correlatividades de la misma.

<a name="inscripciones"></a>
## Inscripciones

La inscripción de un alumno a una carrera se compone de dos partes.

### Primera parte

Se debe indicar la carrera, el plan de estudio de la cual dispone la carrera, y la modalidad a cursar.

![image](/imagenes/documentacion/formulario_inscripcion_1.png)

### Primera parte

Una vez seleccionada la carrera. Se debe completar la informacion necesaria para generar el plan de pagos y si posee alguna beca.

- Año de inscripcion del alumno. Tambien es el año de generacion del plan de pagos
- Monto de la matricula
- Monto por cuota
- Monto por vencimiento de interes
- Cantidad de cuotas a generar
- Fecha de la primera cuota. Aqui tambien se genera la obligacion del tipo matricula a pagar
- Dias de vencimiento de una cuota. Una vez vencido se pierde el beneficio de un descuento (Sujeto a dias previos del vencimiento)
- Beca del alumno y su porcentaje a aplicar

![image](/imagenes/documentacion/formulario_inscripcion_2.png)

> {info} La finalizacion del registro de una nueva inscripcion, notifica al alumno por medio del correo electronico su nueva inscripcion, y a la sede por medio de notificacion en el sistema.

<a name="planes_pago"></a>
## Planes de Pago

El plan de pagos fue pensado para generar cuotas,matriculas e intereses dentro de una año de cursado del alumno. Estas obligaciones deben ser pagadas para actualizar los saldos a hoy y total. En caso de haber saldo de un alumno, el sistema notifica la misma a lo largo de las funciones que ofrece el sitema sobre un alumno

- Año del plan
- Monto de la matricula
- Monto por cuota
- Monto por vencimiento de interes
- Cantidad de cuotas a generar
- Fecha de la primera cuota. Aqui tambien se genera la obligacion del tipo matricula a pagar
- Dias de vencimiento de una cuota. Una vez vencido se pierde el beneficio de un descuento (Sujeto a dias previos del vencimiento)
- Beca del alumno y su porcentaje a aplicar

![image](/imagenes/documentacion/formulario_plan_pago.png)

<a name="pagos"></a>
## Pagos

Existen distintas vistas para poder saldar el plan de pagos. Pero en ecencia son las mismas.

- Importe a pagar
- Fecha de pago
- Descripcion
- Fomra de pago: si es cheque se despliega mas campos.
- Numero de recibo
- Aplicar intereses: el proceso de pago toma en cuenta los intereses, generados por las cuotas impagas, en el armado y distribución del monto.
- Bonificar cuota: dado la cuota, si la fecha de imputacion es menor a X (CANTIDAD BONIFICADO VARIABLE) dias antes del vencimiento y el monto es superior o igual al saldo de la cuota menos el monto bonficable ($MONTO BONIFICADO VARIABLE). Entonces para esa cuota se crea una bonificacion.

Tambien hay distintos tipos de pago:
- Pago a cuota. Es la obligacion mensual del alumno. Si una cuota vence, este genera otro tipo de obligacion llamado interes asociado a la cuota, que incrementa el saldo del plan. Registra movimiento de ingreso en caja.
- Pago bonificado. No registra movimientos de caja pero si el descuento en el saldo.
- Pago matricula. Obligacion inicial en el plan de pagos.

![Pago a cuota](/imagenes/documentacion/formulario_pago_1.png)
![Pago a cuota](/imagenes/documentacion/formulario_pago_2.png)
![Pago bonificado](/imagenes/documentacion/formulario_pago_3.png)
![Pago a matricula](/imagenes/documentacion/formulario_pago_4.png)

> {info} Al registrar el pago, el sistema genera su recibo correspondiente (si el tipo de pago lo permite) e incrementa la cantidad de recibos realizados en la sede.

