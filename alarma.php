<?php
require_once 'conexion.php';
require_once 'jwt.php';
date_default_timezone_set ('America/Mexico_City');

/********BLOQUE DE ACCESO DE SEGURIDAD */
$headers = apache_request_headers();
$tmp = $headers['Authorization'];
$jwt = str_replace("Bearer ", "", $tmp);
if(JWT::verify($jwt, Config::SECRET) > 0){
    header("HTTP/1.1 401 Unauthorized");
    exit;
}

$user = JWT::get_data($jwt, Config::SECRET)['user'];
/*** BLOQUE WEB SERVICE REST */
$metodo = $_SERVER["REQUEST_METHOD"];
switch($metodo){
    case 'GET':
            $c = conexion();
            //isset($_GET['id'])
            $s = $c->prepare("SELECT * FROM alarma WHERE hora = :h ");
            $s->bindValue(":h", date("H:i"));
            $s->execute();
            $s->setFetchMode(PDO::FETCH_ASSOC);
            $r = $s->fetch();
            $s = $c->prepare("SELECT * FROM alimento WHERE id = 1 ");
            $s->execute();
            $s->setFetchMode(PDO::FETCH_ASSOC);
            $d = $s->fetch();
            header("http/1.1 200 ok");
            if($r){
                $r = ["resultado" => $r["extra"]."","carga" => $r["tipo"], "alimentar" => $d["alimentar"],"carga2" => $d["tipo"]];
            }else{
                $r = ["resultado" => "0", "alimentar" => $d["alimentar"],"carga2" => $d["tipo"]];
            }
            if($d["alimentar"] == "si"){
                $s = $c->prepare("UPDATE alimento SET alimentar='no' WHERE id=1");
                $s->execute();
            }
            echo json_encode($r);
        break;
        default:
        header("HTTP/1.1 405 Method Not Allowed");
}