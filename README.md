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

## ¿Por qué elegir este servicio?

- ✅ **100% gratuito**: No tiene coste alguno.
- ✅ **Completamente tuyo**: Usa tu propio dominio para gestionar el DNS dinámico.
- ✅ **Ideal para IPs dinámicas**: Perfecto para servidores en casa, cámaras, MikroTiks y más.
- ✅ **Fácil de usar**: Un solo comando y listo.

## Comunidad

Este servicio fue diseñado pensando en la comunidad de **HestiaCP Spain**, con el objetivo de facilitar la gestión de DNS dinámicos en cualquier entorno.

Si aún no formas parte, únete al grupo de [HestiaCP España](https://t.me/HestiaCPSpain).
