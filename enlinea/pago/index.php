<?php
include_once '../../includes/constants.php';
$propietario = new propietario();
if (!isset($_GET['id'])) {    
    $propietario->esPropietarioLogueado();
    $session = $_SESSION;
}
$bitacora = new bitacora();

$accion = isset($_GET['accion']) ? $_GET['accion'] : "listar";


switch ($accion) {
    
    case "cancelacion":
        $titulo = $_GET['id'] . ".pdf";
        $content = 'Content-type: application/pdf';
        $url = ROOT . "cancelacion.gastos/" . $_GET['id'] . ".pdf";
        header('Content-Disposition: attachment; filename="' . $titulo . '"');
        header($content);
        readfile($url,false);
        break;
    
    case "listarRecibosCancelados":
        $propiedad = new propiedades();
        $inmuebles = new inmueble();
        $pagos = new pago();

        $propiedades = $propiedad->propiedadesPropietario($_SESSION['usuario']['cedula']);
        $cuenta = Array();

        if ($propiedades['suceed'] == true) {

            foreach ($propiedades['data'] as $propiedad) {

                $inmueble = $inmuebles->ver($propiedad['id_inmueble']);
                $pago = $pagos->listarCancelacionDeGastos($propiedad['id_inmueble'], $propiedad['apto']);
                
                if ($pago['suceed'] == true) {
                    $bitacora->insertar(Array(
                        "id_sesion"=>$session['id_sesion'],
                        "id_accion"=> 12,
                        "descripcion"=>count($pago['data'])." recibos(s) registrado(s).",
                    ));
                    for ($index = 0; $index < count($pago['data']); $index++) {
                        
                        $filename = "../../cancelacion.gastos/" . $pago['data'][$index]['numero_factura'] . ".pdf";
                        $pago['data'][$index]['recibo'] = file_exists($filename);
                        
                    }
                    
                    $cuenta[] = Array("inmueble" => $inmueble['data'][0],
                        "propiedades" => $propiedad,
                        "cuentas" => $pago['data']);
                }
            }
        }
        
        echo $twig->render('enlinea/pago/cancelacion.gastos.html.twig', array("session" => $session,
            "cuentas" => $cuenta));


        break; 
    
    case "ver":
        $propiedad = new propiedades();
        $inmuebles = new inmueble();
        $pagos = new pago();

        $propiedades = $propiedad->propiedadesPropietario($_SESSION['usuario']['cedula']);
        $cuenta = Array();
        
        if ($propiedades['suceed'] == true) {
            
            foreach ($propiedades['data'] as $propiedad) {
                
                $legal = $propiedad['meses_pendiente'] > MESES_COBRANZA;
                
                $inmueble = $inmuebles->ver($propiedad['id_inmueble']);
                $pago = $pagos->listarPagosProcesados($propiedad['id_inmueble'], $propiedad['apto'], 5);
                
                if ($pago['suceed'] == true) {
                    $bitacora->insertar(Array(
                        "id_sesion"=>$session['id_sesion'],
                        "id_accion"=> 12,
                        "descripcion"=>count($pago['data'])." recibos(s) registrado(s).",
                    ));
                    for ($index = 0; $index < count($pago['data']); $index++) {
                        $filename = "../../cancelacion.gastos/" . $pago['data'][$index]['id_factura'] . ".pdf";
                        $pago['data'][$index]['recibo'] = file_exists($filename);
                    }

                    $cuenta[] = Array("inmueble" => $inmueble['data'][0],
                        "propiedades" => $propiedad,
                        "cuentas" => $pago['data'],
                        "legal"=>$legal
                            );
                }
            }
        }

        echo $twig->render('enlinea/pago/cancelacion.html.twig', array("session" => $session,
            "cuentas" => $cuenta));


        break; 
    
    case "guardar":
        $pago = new pago();
        $data = $_POST;
        if (count($data) > 0) {
            unset($data['registrar']);
            $data['fecha']=date("Y-m-d H:i:00 ", time());
            $exito = $pago->registrarPago($data);
            $bitacora->insertar(Array(
                "id_sesion"=>$session['id_sesion'],
                "id_accion"=> 9,
                "descripcion"=>$data['numero_documento']." >".$exito['mensaje'],
            ));
        } else {
            header("location:" . URL_SISTEMA . "/pago/registrar");
            return;
        }
        
        echo json_encode($exito);
        
        break;
    
    case "registrar":
    case "listar":
    default :
        $propiedad  = new propiedades();
        $facturas   = new factura();
        $inmuebles  = new inmueble();
        $resultado  = [];
        $cuenta     = [];

        if ($accion == 'guardar') {
            $resultado = $exito;
        }
        
        $propiedades = $propiedad->propiedadesPropietario($_SESSION['usuario']['cedula']);

        $data = [
            'id_sesion'     => $session['id_sesion'],
            'id_accion'     => 8,
            'descripcion'   => 'Inicio del proceso',
        ];

        $bitacora->insertar($data);
        
        if ($propiedades['suceed'] == true) {

            foreach ($propiedades['data'] as $propiedad) {

            $inmueble = $inmuebles->ver($propiedad['id_inmueble']);

            $factura = $facturas->estadoDeCuenta($propiedad['id_inmueble'], $propiedad['apto']);
            
                if ($factura['suceed'] == true) {
                    
                    for ($index = 0; $index < count($factura['data']); $index++) {
                        $filename = "../avisos/" . $factura['data'][$index]['numero_factura'] . ".pdf";
                        $factura['data'][$index]['aviso'] = file_exists($filename);
                        $r = pago::facturaPendientePorProcesar(
                                $factura['data'][$index]['periodo'], 
                                $factura['data'][$index]['id_inmueble'], 
                                $factura['data'][$index]['apto']
                                );
                        if ($r['suceed'] && count($r['data'])>0) {
                            $factura['data'][$index]['pagado'] = 1;
                            $factura['data'][$index]['pagado_detalle']= "<i class='fa fa-calendar-o'></i> ".
                                    Misc::date_format($r['data'][0]['fecha'])."<br>".
                                    strtoupper($r['data'][0]['tipo_pago']." - Ref: ".
                                            $r['data'][0]['numero_documento']."<br>Monto: ".
                                            number_format($r['data'][0]['monto'],2,",","."));
                        } else {
                            $factura['data'][$index]['pagado'] = 0;
                            $factura['data'][$index]['pagado_detalle']='';
                        }

                    }
                    
                    $cuenta[] = [
                        'inmueble'    => $inmueble['data'][0],
                        'propiedades' => $propiedad,
                        'cuentas'     => $factura['data'],
                        'resultado'   => $resultado
                    ];
                }
            }
        }

        $options = [
            'session'     => $session,
            'cuentas'     => $cuenta,
            'accion'      => $accion,
            'usuario'     =>$session['usuario'],
            'propiedades' =>$propiedades['data']
        ];
        echo $twig->render('enlinea/pago/formulario.html.twig', $options );
        break; 

    case "listaPagosDetalle":
        $pagos = new pago();
        $pago_detalle = $pagos->detalleTodosPagosPendientes();
        if ($pago_detalle['suceed'] && count($pago_detalle['data']) > 0) {
            echo "id_pago,id_inmueble,id_apto,monto,id_factura<br>";
            foreach ($pago_detalle['data'] as $value) {
                echo $value['id_pago'] . ",";
                echo "\"".$value['id_inmueble']."\",";
                echo $value['id_apto'] . ",";
                echo $value['monto'] * 100 . ",";
                echo "\"".$value['id_factura']."\"";
                echo "<br>";
            }
        }
        break; 

    case "listaPagosMaestros":
        $pagos = new pago();
        $pagos_maestro = $pagos->listarPagosPendientes();


        if ($pagos_maestro['suceed'] && count($pagos_maestro['data']) > 0) {
            echo "id,fecha,tipo_pago,numero_documento,fecha_documento,monto,banco_origen,";
            echo "banco_destino,numero_cuenta,estatus,email,enviado,telefono<br>";
            foreach ($pagos_maestro['data'] as $pago) {
                echo $pago['id'] . ",";
                echo Misc::date_format($pago['fecha']) . ",";
                echo strtoupper($pago['tipo_pago']) . ","; 
               echo $pago["numero_documento"] . ",";
                echo Misc::date_format($pago["fecha_documento"]) . ",";
                echo $pago["monto"] * 100 . ",";
                echo $pago["banco_origen"] . ",";
                echo $pago["banco_destino"] . ",";
                echo str_replace("-", "", "#".$pago["numero_cuenta"]) . ",";
                echo strtoupper($pago["estatus"]) . ",";
                echo $pago["email"] . ",";
                echo $pago["enviado"] . ",";
                echo $pago["telefono"];
                echo "<br>";
            }
        }
        break; 

    case "listarPagosPendientes":
        $pagos = new pago();
        $pagos_maestro = $pagos->listarPagosPendientes();
        
        if ($pagos_maestro['suceed'] && count($pagos_maestro['data']) > 0) {
            
            foreach ($pagos_maestro['data'] as $pago) {

                $pago_detalle = $pagos->detallePagoPendiente($pago['id']);
                
                if ($pago_detalle['suceed'] && count($pago_detalle['data']) > 0) {
                    $enviado = $pago["enviado"] == 0 ? "False" : "True";
                    echo "|" . $pago['id'] . "|";
                    echo Misc::date_format($pago['fecha']) . "|";
                    echo strtoupper($pago['tipo_pago']) . "|";
                    echo $pago["numero_documento"] . "|";
                    echo Misc::date_format($pago["fecha_documento"]) . "|";
                    echo Misc::number_format($pago["monto"]) . "|";
                    echo $pago["banco_origen"] . "|";
                    echo $pago["banco_destino"] . "|";
                    echo $pago["numero_cuenta"] . "|";
                    echo strtoupper($pago["estatus"]) . "|";
                    echo $pago["email"] . "|";
                    echo $enviado . "|";
                    echo $pago["telefono"] . "|";
                    // --
                    foreach ($pago_detalle['data'] as $value) {
                        echo $value['id_inmueble'] . "|";
                        echo $value['id_apto'] . "|";
                        echo Misc::number_format($value['monto']) . "|";
                        echo $value['id_factura'] . "|";
                        echo $value['periodo'] . "|";
                    }
                    echo "<br>";
                
                }
            
            }
        

        } else {
            echo "0";
        }


        break; // </editor-fold>

    case "confirmar":

        $pago = new pago();
        $id = $_GET['id'];
        $estatus = $_GET['estatus'];
        $r = $pago->procesarPago($id, $estatus);
        echo $r;
        break; 
   
    
    case "reenviarEmailRegistroPago":
        $pago = new pago();
        $id = $_GET['id'];
        $pago->enviarEmailPagoRegistrado($id);
        break; 
    
        
    case "listaRecibosCanceladosNoPublicados":
        $pagos = new pago();
        $pago = $pagos->listarPagosProcesadosWeb();
        
        if ($pago['suceed']) {
            $n = 0;
            $i = 0;
            $apto='';
            foreach ($pago['data'] as $r) {
                $filename = "../../cancelacion.gastos/" . $r['id_factura'] . ".pdf";
                
                if (!file_exists($filename)) {
                    if ($i < 6 && $apto!=$r['id_apto']) {
                        echo $r['id_factura'] . "|" . $r['id_inmueble'] . "|" . $r['id_apto'] . "<br>";
                        $n++;
                        $i++;
                    }
                    if ($i == 5) {
                        $apto=$r['id_apto'];
                        $i=0;
                    }
                }
            }
            echo "Total Recibos: " . $n;
        }
        break; 
        
    case "enviarEmailRegistroPagoNoEnviado":
        $pago = new pago();
        $lista = $pago->listarPagosEmailRegisroNoEnviado();


        if ($lista['suceed'] && count($lista['data']) > 0) {
            foreach ($lista['data'] as $registro) {
                $pago->enviarEmailPagoRegistrado($registro['id']);
                echo '<br>';
            }
        } else {
            echo 'No hay email que reenviar en este momento';
        }
        break;

    case "listarPagosProcesadosGeneral":
        $pagos = new pago();
        $desde = null;
        $hasta = null;
        $codigo_inmueble = null;
        if (isset($_GET['desde'])) {
            $desde = $_GET['desde'];
        }
        if (isset($_GET['hasta'])) {
            $hasta = $_GET['hasta'];
        }
        if (isset($_GET['inmueble'])) {
            $codigo_inmueble = $_GET['inmueble'];
        }


        $pagos_maestro = $pagos->listarPagosProcesadosRangoFechas($desde, $hasta, $codigo_inmueble);


        if ($pagos_maestro['suceed'] && count($pagos_maestro['data']) > 0) {


            foreach ($pagos_maestro['data'] as $pago) {

                $pago_detalle = $pagos->detallePagoPendiente($pago['id']);


                if ($pago_detalle['suceed'] && count($pago_detalle['data'] > 0)) {
                    $enviado = $pago["enviado"] == 0 ? "False" : "True";
                    echo "|" . $pago['id'] . "|";
                    echo Misc::date_format($pago['fecha']) . "|";
                    echo strtoupper($pago['tipo_pago']) . "|";
                    echo $pago["numero_documento"] . "|";
                    echo Misc::date_format($pago["fecha_documento"]) . "|";
                    echo Misc::number_format($pago["monto"]) . "|";
                    echo $pago["banco_origen"] . "|";
                    echo $pago["banco_destino"] . "|";
                    echo $pago["numero_cuenta"] . "|";
                    echo strtoupper($pago["estatus"]) . "|";
                    echo $pago["email"] . "|";
                    echo $enviado . "|";
                    echo $pago["telefono"] . "|";
                    // --
                    foreach ($pago_detalle['data'] as $value) {
                        echo $value['id_inmueble'] . "|";
                        echo $value['id_apto'] . "|";
                        echo Misc::number_format($value['monto']) . "|";
                        echo $value['id_factura'] . "|";
                        echo $value['periodo'] . "|";
                    }
                    echo "<br>";
                }
            }
        } else {
            echo "0";
        }
        break; 
}