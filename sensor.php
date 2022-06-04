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
        $s = $c->prepare("SELECT * FROM sensores ORDER BY ID DESC");
        //$s->bindValue(":u", $user);
            //$s->bindValue(":id", $_GET['id']);
        $s->execute();
        $s->setFetchMode(PDO::FETCH_ASSOC);
        $r = $s->fetch();
        if($r["valor"] > 12){
            $r = ["resultado" => "1"];
        }else{
            $r = ["resultado" => "0"];
        }
        //echo json_encode($r);
        header("http/1.1 200 ok");
        echo json_encode($r);
    break;
    case 'POST':
        if(isset($_POST['valor'])){
            $c = conexion();
            $s = $c->prepare("INSERT INTO sensores (user, valor, fecha) VALUES (:u, :v, NOW())");
            $s->bindValue(":u", $user);
            $s->bindValue(":v", $_POST['valor']);
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
            if(isset($_GET['alimentar']) && isset($_GET['tipo'])){
                $c = conexion();
                $s = $c->prepare("UPDATE alimento SET alimentar=:a, tipo=:t WHERE id=1");
                $s->bindValue(":a", $_GET['alimentar']);
                $s->bindValue(":t", $_GET['tipo']);
                $s->execute();
                if($s->rowCount()>0){
                    header("http/1.1 200 ok");
                    echo json_encode(array("Update" => "y", "id" => $c->lastInsertId()));
                }else{
                    header("http/1.1 400 bad request");
                    echo json_encode(array("Update" => "n"));
                }
            }else{
                header("HTTP/1.1 400 Bad Request");
                echo "Faltan datos";
            }
            break;
    default:
        header("HTTP/1.1 405 Method Not Allowed");
}