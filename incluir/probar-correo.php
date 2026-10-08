<?php
/*
 * Prueba de envío desde el servidor, con la misma configuración que el formulario.
 * Solo desde la línea de comandos:  php incluir/probar-correo.php destinatario@dominio
 * (incluir/ no es accesible desde la web.)
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/correo.php';
$destino = $argv[1] ?? '';
if (!filter_var($destino, FILTER_VALIDATE_EMAIL)) { fwrite(STDERR, "Uso: php incluir/probar-correo.php destinatario@dominio\n"); exit(2); }
$c = config_correo();
echo 'Modo: ' . ($c['modo'] ?? 'mail') . (isset($c['buzon']) ? ' · buzón ' . $c['buzon'] : '') . "\n";
[$ok, $detalle] = enviar_correo($destino, 'Prueba de envío de la web de Vaypatel', "Mensaje de prueba enviado desde el servidor de la web el " . date('d/m/Y H:i') . ".\n", $destino, 'Prueba', 'web@vaypatel.com');
echo $ok ? "Enviado correctamente.\n" : "ERROR: $detalle\n";
exit($ok ? 0 : 1);
