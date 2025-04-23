<?php
// Configuración de la base de datos
$host = 'localhost';  // Cambia esto si tu base de datos está en otro servidor
$db   = 'eventos_db'; // Nombre de la base de datos
$user = 'root';       // Nombre de usuario de la base de datos (por defecto para XAMPP es 'root')
$pass = '';           // Contraseña del usuario de la base de datos (por defecto para XAMPP es vacío)
$charset = 'utf8mb4'; // Codificación de caracteres

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Manejo de errores
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Modo de recuperación de datos
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Preparar sentencias de forma segura
];

try {
    // Crear una instancia de PDO
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Captura errores y muestra mensaje
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
