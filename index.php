<?php
include_once 'includes/constants.php';
if (!isset($_GET['accion'])) {
    echo $twig->render('index.new.html.twig', ["className" => 'white-transparent', "logo" => 'white-logo-web']);

} else {
    echo $twig->render($_GET['accion'].'.html.twig', ["className" => '', "logo" => 'color-logo-web']);

}
//echo $twig->render('suspendido.html.twig');