<?php
$host = "127.0.0.1";
$dbname = "u271483998_YZLFc";
$username = "root";
$password = "";

/* define( 'DB_USER', 'u271483998_WM97i' );
define( 'DB_PASSWORD', 'rXFTk1Vnr0' ); */

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
