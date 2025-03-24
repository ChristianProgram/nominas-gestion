<?php
$host = 'localhost';
$dbname = 'checadora';
$username = 'root';
$password = 'admin1234';

try {
    // Se usa $pdo como variable de conexión
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
