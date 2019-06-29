ferozo 
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wp600062_alumnos
DB_USERNAME=wp600062_alumno
DB_PASSWORD=Alumnos_web2015

ionic cordova build android --prod --release

jarsigner -verbose -sigalg SHA1withRSA -digestalg SHA1 -keystore alumno.keystore platforms\android\app\build\outputs\apk\release\app-release-unsigned.apk alumno -storepass alumno

C:\Users\nicol\AppData\Local\Android\Sdk\build-tools\28.0.3\zipalign -v 4 platforms\android\app\build\outputs\apk\release\app-release-unsigned.apk alumno.apk


keytool -genkey -v -keystore alumno.keystore -alias alumno -keyalg RSA -keysize 2048 -validity 10000