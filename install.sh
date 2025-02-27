#!/bin/bash

# Función para colorear el texto
function color_text {
    case "$1" in
        red) echo -e "\033[31m$2\033[0m" ;;
        green) echo -e "\033[32m$2\033[0m" ;;
        yellow) echo -e "\033[33m$2\033[0m" ;;
        blue) echo -e "\033[34m$2\033[0m" ;;
        *) echo "$2" ;;
    esac
}

color_text blue "Descargando el archivo update.php..."
curl -s -o update.php https://raw.githubusercontent.com/aitorroma/hestiacp-ddns/refs/heads/main/update.php

# Ruta al fichero de configuración de la API
API_FILE="/usr/local/hestia/data/api/ddns"

# Crear el fichero si no existe
if [ ! -f "$API_FILE" ]; then
    color_text yellow "Creando el fichero $API_FILE..."
    sudo touch "$API_FILE"
else
    color_text green "El fichero $API_FILE ya existe."
fi

# Escribir configuraciones en el fichero
color_text blue "Configurando el fichero..."
sudo bash -c "cat > $API_FILE <<EOL
ROLE='user'
COMMANDS='v-list-dns-records,v-change-dns-record,v-add-dns-record,v-delete-dns-record'
EOL"

# Establecer permisos y propietario adecuados
color_text blue "Estableciendo permisos y propietario..."
sudo chmod 600 "$API_FILE"
sudo chown root:root "$API_FILE"

color_text green "Fichero de configuración creado y configurado correctamente: $API_FILE"

# Extraer username, domain y subdominio desde el path actual
current_path=$(pwd)
username=$(echo "$current_path" | awk -F'/' '{print $3}') # Extraer el usuario desde el path
host=$(basename "$(dirname "$(pwd)")")                   # Extraer el host completo (ejemplo: ddns.myhivedns.pro)
subdomain=$(echo "$host" | awk -F'.' '{print $1}')       # Extraer el subdominio (ejemplo: ddns)
domain=$(echo "$host" | sed "s/^$subdomain\.//")         # Extraer el dominio principal (ejemplo: myhivedns.pro)

color_text blue "Usuario detectado: $username"
color_text blue "Dominio detectado: $domain"
color_text blue "Subdominio detectado: $subdomain"

# Ejecutar v-add-access-key
color_text yellow "Generando clave de acceso para el usuario: $username..."
access_key_output=$(v-add-access-key "$username" ddns 'DDNS_Support')

# Comprobar si el comando se ejecutó correctamente
if [[ $? -ne 0 ]]; then
    color_text red "Error al generar la clave de acceso. Verifique los permisos y el usuario."
    exit 1
fi

# Extraer claves del resultado
access_key_id=$(echo "$access_key_output" | grep "ACCESS_KEY_ID:" | awk '{print $2}')
secret_access_key=$(echo "$access_key_output" | grep "SECRET_ACCESS_KEY:" | awk '{print $2}')
hostname=$(v-list-sys-info json | jq -r '.sysinfo.HOSTNAME')

# Verificar si las claves fueron generadas correctamente
if [[ -z "$access_key_id" || -z "$secret_access_key" || -z "$hostname" ]]; then
    color_text red "Error al extraer claves o hostname. Por favor, revise la salida."
    exit 1
fi

# Ruta del archivo credentials.ini en el directorio actual
credentials_file="./credentials.ini"

# Crear o sobrescribir el archivo credentials.ini
color_text blue "Creando el archivo $credentials_file..."
cat > "$credentials_file" <<EOL
hestia_url=https://$hostname:8083
access_key=$access_key_id
secret_key="$secret_access_key"
domain=$domain
subdomain=$subdomain
excluded_subdomains=${host%%.*},www,mail,ftp,webmail
user=$username
EOL

# Ajustar permisos del archivo
chown $username:$username "$credentials_file"
chown $username:$username update.php
chmod 600 "$credentials_file"
color_text green "Archivo $credentials_file creado correctamente."

# Mostrar mensaje final con ejemplo de configuración y uso
color_text green "Configuración completada."
sleep 3
clear
echo
echo
color_text green "Puedes añadir el siguiente comando en MikroTik para programar el DDNS update:"
echo
color_text yellow "/system scheduler add name=\"ddns_update\" on-event=\"/tool/fetch mode=https url=\\\"https://$host/update.php?key=$access_key_id&subdomain=prueba\\\"\" start-date=jan/01/1970 start-time=startup interval=1h comment=\"\" disabled=no"
echo
color_text blue "Nota: Recuerda reemplazar \"prueba\" en el comando con el nombre del subdominio que desees utilizar. Esto configurará correctamente el subdominio como [subdomain].$domain."
echo
color_text green "Puedes probar la URL generada utilizando el siguiente comando curl:"
echo
color_text yellow "curl -X GET \"https://$host/update.php?subdomain=prueba&key=$access_key_id\""
