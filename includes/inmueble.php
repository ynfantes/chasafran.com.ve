<?php

/**
 * Clase que mantiene la tabla inmueble
 *
 * @autor   Edgar Messia
 * @static  
 * @package     Valoriza2.Framework
 * @subpackage	FileSystem
 * @since	1.0
 */

class inmueble extends db implements crud {

    const tabla = "inmueble";

    public function actualizar($id, $data){
        return db::update(self::tabla, $data, array("id" => $id));
    }

    public function borrar($id){
        return db::delete(self::tabla, array("id" => $id));
    }

    /**
     * Inserta el contenido en la tabla propietarios
     *
     * @param	Array	$data	Arreglo con la data
     * 
     * @return	Array	Retorna arreglo con parámetos del resultado
     * @since	1.0
     */
    public function insertar($data){
        return db::insert(self::tabla, $data,"IGNORE");
    }

    public function listar(){
       return db::select("*", self::tabla);
    }
    
    public function ver($id){
        return db::select("*",self::tabla,array("id"=>$id));
    }

    public function borrarTodo() {
        return db::delete(self::tabla);
    }
    
    public function estadoDeCuenta($id) {
        return db::select("*","inmueble_deuda_confidencial",Array("id_inmueble"=>$id));
    }
    
    public function insertarEstadoDeCuentaInmueble($data) {
        return db::insert("inmueble_deuda_confidencial", $data,"IGNORE");
    }
    
     public function movimientoFacturacionMensual($inmueble) {
       $query = "select facturacion_mensual.*, inmueble.nombre_inmueble from facturacion_mensual join inmueble ON facturacion_mensual.id_inmueble = inmueble.id where id_inmueble='$inmueble' and periodo >= date_add((select max(periodo) from facturacion_mensual where id_inmueble='$inmueble'), INTERVAL -7 MONTH) order by periodo ASC";
       //return db::select("*","facturacion_mensual",array("id_inmueble"=>$inmueble),"",Array("periodo" =>"asc"));
       return db::query($query);
    }
    
    public function movimientoCobranzaMensual($inmueble) {
        $query = "select cobranza_mensual.*,inmueble.nombre_inmueble from cobranza_mensual join inmueble on cobranza_mensual.id_inmueble = inmueble.id where id_inmueble='$inmueble' and periodo >= date_add((select max(periodo) from facturacion_mensual where id_inmueble='$inmueble'), INTERVAL -7 MONTH) order by periodo ASC";
        return db::query($query);
        //return db::select("*","cobranza_mensual",array("id_inmueble"=>$inmueble),"",Array("periodo" =>"asc"));
    }
    
    public function insertarFacturacionMensual($data) {
        $query = "insert into facturacion_mensual(id_inmueble,periodo,facturado) "
                . "VALUES('".$data['id_inmueble']."','".$data['periodo']."','".$data['facturado']."') ON DUPLICATE KEY "
                . "UPDATE facturado='".$data['facturado']."'";
        
        return db::exec_query($query);
    }
    
    public function insertarCobranzaMensual($data) {
        $query = "insert into cobranza_mensual(id_inmueble,periodo,monto) "
                . "VALUES('".$data['id_inmueble']."','".$data['periodo']."','".$data['monto']."') ON DUPLICATE KEY "
                . "UPDATE monto='".$data['monto']."'";
        
        return db::exec_query($query);
    }
    
    public function insertarGrupo($data) {
        return db::insert("grupo", $data,"IGNORE");
    }
    
    public function insertarGrupoPropietario($data) {
        return db::insert("grupo_propietario", $data,"IGNORE");
    }
}