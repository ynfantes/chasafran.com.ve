<?php
include './mailto.php';
define("SMTP_SERVER","mail.administradora-figueira.com.ve");
define("PORT",25);
define("USER_MAIL","info@administradora-figueira.com.ve");
define("PASS_MAIL","figueira5231");
define("SMTP",2);
$mail = new mailto(SMTP);
echo("enviando......<br>");
$r = $mail->enviar_email("Prueba", "Este es un mensaje de prueba", '', "ynfantes@gmail.com", "Edgar Messia");
echo("enviado");                    
if ($r=='') {
    echo(".- Mensaje enviado a Ok!\n");
} else {
    echo(".- Mensaje enviado a Falló\n");
}
