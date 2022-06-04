<?php
require_once 'conexion.php';
require_once 'jwt.php';

/*** BLOQUE WEB SERVICE REST */
$metodo = $_SERVER["REQUEST_METHOD"];
switch($metodo){
    case 'POST':
        if(isset($_POST['nombre']) && isset($_POST['apellidos']) && isset($_POST['telefono']) && isset($_POST['user']) && isset($_POST['mascota']) && isset($_POST['raza']) && isset($_POST['pass'])){
            $c = conexion();
            $s = $c->prepare("INSERT INTO registro (nombre, apellidos, telefono, user, mascota, raza, pass) VALUES (:n, :a, :t, :u, :m, :r, :p)");
           // $s = $c->prepare("INSERT INTO users (user, pass) VALUES (:n, :p)");
            $s->bindValue(":n", $_POST['nombre']);
            $s->bindValue(":a", $_POST['apellidos']);
            $s->bindValue(":t", $_POST['telefono']);
            $s->bindValue(":u", $_POST['user']);
            $s->bindValue(":m", $_POST['mascota']);
            $s->bindValue(":r", $_POST['raza']);
            $s->bindValue(":p", sha1($_POST['pass']));
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
        if(isset($_POST['nombre']) && isset($_POST['pass'])){
            $c = conexion();
            $s = $c->prepare("INSERT INTO users (user, pass) VALUES (:n,:p)");
            $s->bindValue(":n", $_POST['nombre']);
            $s->bindValue(":p", sha1($_POST['pass']));
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
    
    default:
        header("HTTP/1.1 405 Method Not Allowed");
}