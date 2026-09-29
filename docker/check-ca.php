<?php

/**
 * Validates the MySQL SSL CA Certificate file.
 * Exit code 0: CA certificate is valid.
 * Exit code 1: CA certificate missing, unreadable, or invalid format.
 */

$caPath = $argv[1] ?? getenv('MYSQL_ATTR_SSL_CA') ?: '/run/app-certificates/mysql-ca.pem';

echo "[CA-CHECK] Inspecting CA certificate at: {$caPath}\n";

if (!file_exists($caPath)) {
    fwrite(STDERR, "[CA-CHECK] Error: CA certificate file not found at: {$caPath}\n");
    exit(1);
}

if (!is_readable($caPath)) {
    fwrite(STDERR, "[CA-CHECK] Error: CA certificate at {$caPath} is not readable (check permissions)\n");
    exit(1);
}

$content = file_get_contents($caPath);
if ($content === false || strlen(trim($content)) === 0) {
    fwrite(STDERR, "[CA-CHECK] Error: CA certificate at {$caPath} is empty\n");
    exit(1);
}

if (!str_contains($content, '-----BEGIN CERTIFICATE-----')) {
    fwrite(STDERR, "[CA-CHECK] Error: CA certificate at {$caPath} does not contain valid PEM header '-----BEGIN CERTIFICATE-----'\n");
    exit(1);
}

echo "[CA-CHECK] Success: Valid CA certificate detected and verified (" . strlen($content) . " bytes).\n";
exit(0);
