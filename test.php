<?php
require_once "conexion.php";

// Verificar el inicio de sesión
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $query = "SELECT * FROM usuarios WHERE correo = '$username' AND password = '$password' LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();

    if ($stmt->rowCount() > 0) {
        $message = "success";
        session_start();
        $_SESSION["usertype"] = $result["type"];
    } else {
        $message = "error";
    }
    echo $message;
}
