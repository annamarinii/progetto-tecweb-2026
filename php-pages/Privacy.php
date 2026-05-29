<?php
require_once '../php-Manager/init_session.php';
require_once '../php-Manager/Tool.php';

$pagina_html = file_get_contents(__DIR__ . '/../html/privacy.html');
$pagina_html = str_replace('[Header]', Tool::buildHeader('privacy'), $pagina_html);
$pagina_html = str_replace('[Footer]', Tool::buildFooter('privacy'), $pagina_html);

echo $pagina_html;
