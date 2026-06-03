<?php
require_once '../php-manager/init_session.php';
require_once '../php-manager/tool.php';

http_response_code(403);

$pagina_html = file_get_contents(__DIR__ . '/../pages/403.html');
$pagina_html = str_replace('[BasePath]', '../', $pagina_html);
$pagina_html = str_replace('[Header]',   Tool::buildHeader('403'), $pagina_html);
$pagina_html = str_replace('[Footer]',   Tool::buildFooter('403'), $pagina_html);

echo $pagina_html;
