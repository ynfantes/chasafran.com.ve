<?php
include './includes/mailto.php';
define("SMTP_SERVER","mail.administradora-figueira.com.ve");
define("PORT",25);
define("USER_MAIL","info@administradora-figueira.com.ve");
define("PASS_MAIL","figueira5231");
define("SMTP",2);
define("NOMBRE_APLICACION","TEST Administradora Figueira");
$mail = new mailto(SMTP);

$r = $mail->enviar_email("Prueba", "Este es un mensaje de prueba", '', "ynfantes@gmail.com", "Edgar Messia");

if ($r=='') {
    echo(".- Mensaje enviado a Ok!\n");
} else {
    echo(".- Mensaje enviado a Falló\n");
}
