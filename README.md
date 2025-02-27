​                                                                                   ![[image-20210506104427925](https://t.me/aitorroma)](https://tva1.sinaimg.cn/large/008i3skNgy1gq8sv4q7cqj303k03kweo.jpg)



# Servicio de DNS Dinámico para HestiaCP 🚀

¡Bienvenido al servicio de DNS Dinámico para HestiaCP, totalmente gratuito y 100% tuyo con tu propio dominio! 🎉

Este servicio está diseñado especialmente para ti si tienes un servidor en casa con IP dinámica, o si gestionas dispositivos como cámaras de seguridad, routers MikroTik, u otros equipos conectados. Con esta herramienta podrás configurar un DNS dinámico de manera rápida, sencilla y gratuita, utilizando tu propio dominio.

## Requisitos

- Un servidor con HestiaCP y el DNS delegado al servidor.
- Un subdominio configurado en el dominio que desees.

## Pasos para configurarlo

1. Crea un subdominio en tu dominio, por ejemplo: `ddns.tudominio.com`.
2. Accede al directorio `public_html` del subdominio.
3. Ejecuta este comando desde el terminal dentro de ese directorio:

```bash
/bin/bash -c "$(curl -fsSL https://ddns.comunidad-hestia.com)"
```

¡Y listo! Este comando configurará automáticamente el servicio para que tengas tu propio DNS dinámico funcionando en minutos.

### ¿Qué hace este script?

- **Descarga el archivo `update.php`** desde un repositorio seguro.
- **Crea y configura** el archivo de configuración de la API en el servidor HestiaCP si no existe.
- **Asigna los permisos necesarios** para asegurar el correcto funcionamiento del servicio.
- **Detecta automáticamente** el nombre de usuario, dominio y subdominio desde el directorio actual.
- **Genera la clave de acceso** para el usuario y crea un archivo `credentials.ini` con las credenciales necesarias.
- **Proporciona los comandos exactos** para configurar el cron job en MikroTik o dispositivos similares.

## ¿Por qué elegir este servicio?

- ✅ **100% gratuito**: No tiene coste alguno.
- ✅ **Completamente tuyo**: Usa tu propio dominio para gestionar el DNS dinámico.
- ✅ **Ideal para IPs dinámicas**: Perfecto para servidores en casa, cámaras, MikroTiks y más.
- ✅ **Fácil de usar**: Un solo comando y listo.

## Ejemplo de uso

Después de ejecutar el script, este te proporcionará los comandos exactos que debes ejecutar para configurar el cron job en MikroTik o dispositivos similares. Por ejemplo, el script generará el siguiente comando:

```bash
/system scheduler add name="ddns_update" on-event="/tool/fetch mode=https url=\"https://$host/update.php?key=$access_key_id&subdomain=prueba\"" start-date=jan/01/1970 start-time=startup interval=1h comment="" disabled=no
```

**Nota**: Recuerda reemplazar "prueba" en el comando con el nombre del subdominio que desees utilizar. Esto configurará el subdominio como `[subdominio].$dominio`.

También puedes probar la URL generada utilizando el siguiente comando `curl`:

```bash
curl -X GET "https://$host/update.php?subdomain=prueba&key=$access_key_id"
```

## Comunidad

Este servicio fue diseñado pensando en la comunidad de **HestiaCP Spain**, con el objetivo de facilitar la gestión de DNS dinámicos en cualquier entorno.

Si aún no formas parte, únete al grupo de [HestiaCP España](https://t.me/HestiaCPSpain).
