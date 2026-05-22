<?php

require '../php-Manager/init_session.php';
require_once '../php-Manager/Tool.php';

$pagina_html = file_get_contents('../html/biglietti.html');

$pagina_html = str_replace('[Header]', Tool::buildHeader('biglietti'), $pagina_html);
$pagina_html = str_replace('[Footer]', Tool::buildFooter('biglietti'), $pagina_html);

echo $pagina_html;
