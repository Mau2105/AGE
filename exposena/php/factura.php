<?php
session_start();
require 'config.php';

// Verificar si hay un id_reserva
if (!isset($_GET['id_reserva'])) {
    die('No se encontró la reserva.');
}

$id_reserva = $_GET['id_reserva'];

// Obtener los detalles de la reserva
$stmt = $pdo->prepare('
    SELECT r.id_reserva, r.evento, r.fecha, r.invitados, r.fecha_reserva, r.confirmado, u.nombre AS usuario_nombre, u.correo 
    FROM reservas r
    JOIN usuarios u ON r.usuario_id = u.id
    WHERE r.id_reserva = ? AND r.confirmado = 1
');
$stmt->execute([$id_reserva]);
$reserva = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar si la reserva existe y está confirmada
if (!$reserva) {
    die('Reserva no confirmada o no encontrada.');
}

// Obtener la tarifa del evento
$evento_stmt = $pdo->prepare('SELECT tarifa FROM tarifas WHERE evento = ?');
$evento_stmt->execute([$reserva['evento']]);
$tarifa = $evento_stmt->fetch(PDO::FETCH_ASSOC)['tarifa'];

// Mostrar la factura
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura - Reserva Evento</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { width: 80%; margin: 0 auto; }
        h1 { text-align: center; }
        table { width: 100%; margin-top: 30px; border-collapse: collapse; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Factura de Reserva</h1>
        <p><strong>Nombre de Usuario:</strong> <?php echo htmlspecialchars($reserva['usuario_nombre']); ?></p>
        <p><strong>Correo Electrónico:</strong> <?php echo htmlspecialchars($reserva['correo']); ?></p>
        <table>
            <tr>
                <th>Evento</th>
                <td><?php echo htmlspecialchars($reserva['evento']); ?></td>
            </tr>
            <tr>
                <th>Fecha de Reserva</th>
                <td><?php echo htmlspecialchars($reserva['fecha']); ?></td>
            </tr>
            <tr>
                <th>Número de Invitados</th>
                <td><?php echo htmlspecialchars($reserva['invitados']); ?></td>
            </tr>
            <tr>
                <th>Estado de Confirmación</th>
                <td><?php echo $reserva['confirmado'] ? 'Confirmado' : 'Pendiente'; ?></td>
            </tr>
        </table>
        <p><strong>Total a Pagar:</strong> $<?php echo number_format($tarifa, 2); ?></p>
        <script>
            window.print();
        </script>
    </div>
</body>
</html>
