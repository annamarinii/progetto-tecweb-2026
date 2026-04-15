<?php

require '../php-dbManager/init_session.php';
/** @var string $destinazione_profilo */

$pagina_html = file_get_contents('../html/biglietti.html');
$pagina_html = str_replace('[link_profilo]', $destinazione_profilo, $pagina_html);


echo $pagina_html;
