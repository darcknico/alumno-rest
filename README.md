ionic cordova build android --prod --release

jarsigner -verbose -sigalg SHA1withRSA -digestalg SHA1 -keystore alumno.keystore platforms\android\app\build\outputs\apk\release\app-release-unsigned.apk alumno -storepass alumno

C:\Users\nicol\AppData\Local\Android\Sdk\build-tools\28.0.3\zipalign -v 4 platforms\android\app\build\outputs\apk\release\app-release-unsigned.apk alumno.apk


keytool -genkey -v -keystore alumno.keystore -alias alumno -keyalg RSA -keysize 2048 -validity 10000

<widget id="ar.com.proyectosinformaticos.alumno" version="0.0.5" xmlns="http://www.w3.org/ns/widgets" xmlns:cdv="http://cordova.apache.org/ns/1.0">


### 2020-04-15
Para este caso agregare los campos necesarios para guardar el estado del alumno en la base de datos.
- Año de cursada: que se da de acuerdo al año lectivo de la ultima materia que fue inscripto
- Porcentaje aprobado: total de materias aprobadas sobre lo que resta al final
- Total examenes realizados
- Total examenes aprobados
- Promedio examen con desaprobados: tomando todos los exámenes realizados por el alumno
- Promedio examen sin desaprobados: tomando solo los que esten en el rango de aprobados

Para el reporte modificare la consulta del listado de inscripciones y agregare los filtros de:
- Desde y Hasta promedio aprobado (sin desaprobados)
- Año lectivo
y en el reporte de inscripciones agregare todos los campos que agregue anteriormente.

Entonces realizare un proceso para que por unica vez se actualice estos valores en los alumnos actuales.
Luego estos valores seran modificados cada vez que ocurra uno de los siguientes eventos:
- Inscripcion a una comision (Masivo/Simple)
- Eliminar la inscripcion de una comision 
- Registro en notas viejo
- Modificacion en un notas viejo
- Eliminacion en notas viejo
- Modificacion en la nota de un alumno en la mesa de examen (Masivo/Simple)
- Eliminacion de la inscripcion de un alumno en la mesa de examen

### 2020-05-07 1.6.2

Modificado
- Api de nuevo pago y movimiento, agregado numero de transaccion para formas de pago con Mercado Pago y Transaccion Bancaria
- Vista previa de pagos, "detallePreparar()" para aceptar opcion ESPECIAL COVID-19