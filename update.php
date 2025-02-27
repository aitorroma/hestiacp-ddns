<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set default timezone
date_default_timezone_set('Europe/Madrid');

// Create logging function
function update_log($log_message, $log_params = []) {
    $log_date = date('Y-m-d H:i:s');
    $log_ip = $log_params['ip'] ?? $_SERVER['REMOTE_ADDR'];
    $log_host = $log_params['host'] ?? '-';
    $log_line = sprintf("[%s] %s, IP: %s, HOST: %s\n", $log_date, $log_message, $log_ip, $log_host);
    file_put_contents('ddns_update.log', $log_line, FILE_APPEND);
    echo $log_message . "\n";
}

// Check method
if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'GET'])) {
    http_response_code(405);
    update_log('ERROR: Wrong request method');
    die();
}

// Load and check credentials
$credentials = parse_ini_file('credentials.ini');
if (empty($credentials['hestia_url']) || empty($credentials['access_key']) || empty($credentials['secret_key']) || empty($credentials['domain']) || empty($credentials['user'])) {
    http_response_code(500);
    update_log('ERROR: Hestia API credentials or domain not set');
    die();
}

// Load excluded subdomains from credentials.ini
$excluded_subdomains = array_map('trim', explode(',', $credentials['excluded_subdomains'] ?? ''));

// Check and get parameters
if (!empty($_REQUEST['subdomain']) && !empty($_REQUEST['key'])) {
    $params = [
        'key' => $_REQUEST['key'],
        'subdomain' => $_REQUEST['subdomain'],
        'domain' => $credentials['domain'],
        'host' => sprintf('%s.%s', $_REQUEST['subdomain'], $credentials['domain']),
        'ip' => empty($_REQUEST['ip']) ? $_SERVER['REMOTE_ADDR'] : $_REQUEST['ip']
    ];
} else {
    http_response_code(400);
    update_log('ERROR: Missing required parameters (key, subdomain)');
    die();
}

// Check excluded subdomains
if (in_array($params['subdomain'], $excluded_subdomains)) {
    http_response_code(403);
    update_log('ERROR: Subdomain is excluded from modification', $params);
    die();
}

// Check API key
if ($params['key'] !== $credentials['access_key']) {
    http_response_code(403);
    update_log('ERROR: Invalid access key', $params);
    die();
}

// Initialize cURL for Hestia API
function hestia_api_request($endpoint, $data) {
    global $credentials;

    $data['hash'] = $credentials['access_key'] . ':' . $credentials['secret_key'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $credentials['hestia_url'] . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // Convertir datos a formato POST
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Manejo de respuestas vacías o código 204
    if (($http_code === 200 || $http_code === 204) && empty($response)) {
        return "OK: No response but operation succeeded.";
    }

    if ($http_code !== 200 && $http_code !== 204) {
        throw new Exception("Hestia API error: " . $response);
    }

    return $response;
}

try {
    // List DNS records
    $list_data = [
        'cmd' => 'v-list-dns-records',
        'arg1' => $credentials['user'],
        'arg2' => $credentials['domain'],
        'arg3' => 'json' // Especificar formato JSON
    ];

    $list_response = hestia_api_request('/api/', $list_data);
    $records = json_decode($list_response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Failed to parse DNS records: " . $list_response);
    }

    // Check if the subdomain exists
    foreach ($records as $record) {
        if ($record['RECORD'] === $params['subdomain'] && $record['TYPE'] === 'A') {
            // Delete the existing record
            $delete_data = [
                'cmd' => 'v-delete-dns-record',
                'arg1' => $credentials['user'],
                'arg2' => $credentials['domain'],
                'arg3' => $record['ID']
            ];
            hestia_api_request('/api/', $delete_data);
            update_log('INFO: Existing DNS record deleted', $params);
        }
    }

    // Create DNS record
    $data = [
        'cmd' => 'v-add-dns-record',
        'arg1' => $credentials['user'],     // Usuario de Hestia
        'arg2' => $credentials['domain'],   // Dominio principal
        'arg3' => $params['subdomain'],     // Subdominio
        'arg4' => 'A',                      // Tipo de registro
        'arg5' => $params['ip'],            // Dirección IP
        'arg6' => 0,                        // Prioridad (por defecto 0)
        'arg7' => '',                       // ID opcional (vacío)
        'arg8' => '',                       // Reiniciar servicio DNS (vacío)
        'arg9' => 3600                      // TTL (por defecto 3600)
    ];

    $response = hestia_api_request('/api/', $data);

    if (strpos($response, 'error') !== false) {
        throw new Exception("Failed to create DNS record: " . $response);
    }

    update_log('OK: DNS record created', $params);
} catch (Exception $e) {
    http_response_code(400);
    update_log('ERROR: ' . $e->getMessage(), $params);
    die();
}

update_log('OK: DNS record updated successfully', $params);
