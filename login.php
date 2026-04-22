<?php

include_once 'includes/constants.php';

//echo $twig->render('mantenimiento.html.twig');
//die();

$propietario = new propietario();

$result = array();
$usercode = '';
$password = '';

if (isset($_POST['usercode']) && isset($_POST['password'])) {
    
    $usercode = $_POST['usercode'];
    $password = $_POST['password'];
    $result = $propietario->login($usercode, $password, 0);
    
    if ($result['suceed']) {
        if ($_SESSION['status'] == 'logueado') {
            header("location:".URL_SISTEMA );
            die();
        }
    }

    $options = ["error"    => isset($result["error"]) ? $result["error"] : '',
                "usercode" => $usercode,
                "password" => $password,
                "logo"     => 'logo-web'
                ];
    echo $twig->render('condominio-en-linea.html.twig', $options);
    //header("location:".ROOT."condominio-en-linea.html?e=1");

} else {
    if (isset($_POST['email'])) {
        $result = $propietario->recuperarContraSena($_POST['email']);
        echo json_encode($result);
    }
}
