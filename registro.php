<?php
require_once 'conexion.php';
require_once 'jwt.php';

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
            $s = $c->prepare("SELECT * FROM alarma WHERE nombre = :u");
            $s->bindValue(":u", $user);
                //$s->bindValue(":id", $_GET['id']);
            $s->execute();
            $s->setFetchMode(PDO::FETCH_ASSOC);
            $r = $s->fetchAll();
            header("http/1.1 200 ok");
            echo json_encode($r);
        break;
    case 'POST':
        if(isset($_POST['hora']) && isset($_POST['extra']) && isset($_POST['tipo'])){
            $c = conexion();
            $s = $c->prepare("INSERT INTO alarma (nombre, hora, extra, tipo) VALUES (:u, :h, :e, :t)");
            $s->bindValue(":u", $user);
            $s->bindValue(":h", $_POST['hora']);
            $s->bindValue(":e", $_POST['extra']);
            $s->bindValue(":t", $_POST['tipo']);
            $s->execute();
            if($s->rowCount()>0){
                header("http/1.1 201 created");
                echo json_encode(array("add" => "y", "id" => $c->lastInsertId()));
            }else{
                header("http/1.1 400 bad request");
                echo json_encode(array("add" => "n"));
            }
        }else{
            header("HTTP/1.1 400 Bad Request");
            echo "Faltan datos";
        }
        break;
        case 'PUT':
            if(isset($_GET['id']) ){
                $sql = "UPDATE alarma SET ";
                (isset($_GET['nombre'])) ? $sql .= "nombre = :n, " : null;
                (isset($_GET['hora'])) ? $sql .= "hora = :h, " : null;
                (isset($_GET['extra'])) ? $sql .= "extra = :e, " : null;
                (isset($_GET['tipo'])) ? $sql .= "tipo = :t, " : null;
                $sql = substr($sql, 0, -2);
                $sql .= " WHERE id = :id";
                $c = conexion();
                $s = $c->prepare($sql);
                (isset($_GET['nombre'])) ? $s->bindValue(":n", $_GET['nombre']) : null;
                (isset($_GET['hora'])) ? $s->bindValue(":h", $_GET['hora']) : null;
                (isset($_GET['extra'])) ? $s->bindValue(":e", $_GET['extra']) : null;
                (isset($_GET['tipo'])) ? $s->bindValue(":t", $_GET['tipo']) : null;
    
                $s->bindValue(":id", $_GET['id']);
                $s->execute();
                if($s->rowCount()>0){
                    header("http/1.1 200 ok");
                    echo json_encode(array("update" => "y"));
                }else{
                    header("http/1.1 400 bad request");
                    echo json_encode(array("update" => "n"));
                }
            }else{
                header("HTTP/1.1 400 Bad Request");
                echo "Faltan datos";
            }
            break;
        case 'DELETE':
            if(isset($_GET['id'])){
                $c = conexion();
                $s = $c->prepare("DELETE FROM alarma WHERE id = :id");
                $s->bindValue(":id", $_GET['id']);
                $s->execute();
                if($s->rowCount()>0){
                    header("http/1.1 200 ok");
                    echo json_encode(array("delete" => "y"));
                }else{
                    header("http/1.1 400 bad request");
                    echo json_encode(array("delete" => "n"));
                }
            }else{
                header("HTTP/1.1 400 Bad Request");
                echo "Faltan datos";
            }
            break;
    default:
        header("HTTP/1.1 405 Method Not Allowed");
}