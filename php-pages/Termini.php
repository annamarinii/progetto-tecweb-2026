<?php
require_once '../php-Manager/init_session.php';
require_once '../php-Manager/Tool.php';

$pagina_html = file_get_contents(__DIR__ . '/../html/termini.html');
$pagina_html = str_replace('[Header]', Tool::buildHeader('termini'), $pagina_html);
$pagina_html = str_replace('[Footer]', Tool::buildFooter('termini'), $pagina_html);

echo $pagina_html;
