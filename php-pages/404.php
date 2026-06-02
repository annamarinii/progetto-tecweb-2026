<?php
require_once '../php-Manager/init_session.php';
require_once '../php-Manager/tool.php';

http_response_code(404);

$pagina_html = file_get_contents(__DIR__ . '/../pages/404.html');
$pagina_html = str_replace('[BasePath]', '../', $pagina_html);
$pagina_html = str_replace('[Header]',   Tool::buildHeader('404'), $pagina_html);
$pagina_html = str_replace('[Footer]',   Tool::buildFooter('404'), $pagina_html);

echo $pagina_html;
