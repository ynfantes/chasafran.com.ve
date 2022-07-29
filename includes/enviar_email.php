<?php
include './constants.php';
include './mailto.php';

$message="Contacto ".NOMBRE_APLICACION."\n";
$message.="Persona: ".$_POST["nombre"]."\n";
$message.="Correo: ".$_POST["email"]."\n-------------------------------\n";
$message.="Te ha escrito: ".$_POST["mensaje"];
$message = stripcslashes(nl2br(htmlentities($message)));
$headers = "Content-Language:es-ve\n";
$headers .= "Content-Type: text/html; charset=iso-8859-1\n";
$headers .= "bcc:ynfantes@gmail.com\n";

$subject = "Contacto administradorachasafran.com.ve";
$headers .= 'From: Contacto <chasafran1@gmail.com>'."\r\n".'Reply-To:'.$_POST["email"]."\r\n" ;
$email = "chasafran1@gmail.com";

$mail = new mailto(SMTP);

//if (mail($email,$subject,$message,$headers)) {
$r = $mail->enviar_email($subject, $message, '', "chasafran1@gmail.com", "Administradora Chasafran");

if ($r=='') {
    echo "<div class=\"alert alert-success fade in\">\n<button id=\"alert-success\" data-dismiss=\"alert\" class=\"close\" 
    type=\"button\">×</button>\n<strong>¡Mensaje enviando con éxito!</strong> A la brevedad
    nos estaremos comunicando con usted.<br>Gracias por contactarnos.\n</div>";
} else {
    echo "<div class=\"alert alert-danger fade in\">\n<button id=\"alert-error\" data-dismiss=\"alert\" class=\"close\" 
    type=\"button\">×</button>\n<strong>¡Ups! Ocurrió un error al tratar de enviar el mensaje</strong>
    Inténtelo nuevamente.<br>Gracias por contactarnos.\n</div>";   
}