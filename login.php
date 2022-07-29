<?php

include_once 'includes/constants.php';

//echo $twig->render('mantenimiento.html.twig');
//die();

$propietario = new propietario();

$result = array();
$cedula = '';
$password = '';

if (isset($_POST['cedula']) && isset($_POST['password'])) {
    
    $cedula = $_POST['cedula'];
    $password = $_POST['password'];
    $result = $propietario->login($cedula, $password, 0);
    
    if ($result['suceed']) {
        
        if ($_SESSION['status'] == 'logueado') {
            header("location:" . URL_SISTEMA );
        }
    }
    echo $twig->render('login.html.twig', array("mensaje" => $result,"cedula"=>$cedula,"password"=>$password));
} else {
    if (isset($_POST['email'])) {
        $result = $propietario->recuperarContraSena($_POST['email']);
        echo json_encode($result);
    }
}
