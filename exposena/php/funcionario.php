<?php
session_start();
require 'config.php';

// Verificar si el usuario ha solicitado cerrar sesión
if (isset($_POST['logout'])) {
    // Destruir la sesión y redirigir al usuario a la página de inicio de sesión
    session_unset();
    session_destroy();
    header('Location: login.php'); // Cambiar 'login.php' por la página de inicio de sesión
    exit();
}

// Obtener el nombre del funcionario desde la sesión
$funcionario_id = $_SESSION['funcionario_id'] ?? null;
$funcionario_nombre = '';

if ($funcionario_id) {
    $stmt = $pdo->prepare('SELECT nombre FROM funcionarios WHERE id = ?');
    $stmt->execute([$funcionario_id]);
    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($funcionario) {
        $funcionario_nombre = $funcionario['nombre'];
    }
}

// Confirmar pago
if (isset($_POST['confirm_payment'])) {
    $id_reserva = $_POST['id_reserva'];

    $stmt = $pdo->prepare('UPDATE reservas SET confirmado = 1, funcionario_id = ? WHERE id_reserva = ?');
    $stmt->execute([$funcionario_id, $id_reserva]);

    $confirmation_message = 'El pago ha sido confirmado por ' . htmlspecialchars($funcionario_nombre) . ' y el evento ha sido reservado.';
}

// Eliminar reserva pendiente
if (isset($_POST['delete_reserva'])) {
    $id_reserva = $_POST['id_reserva'];

    $stmt = $pdo->prepare('DELETE FROM reservas WHERE id_reserva = ?');
    $stmt->execute([$id_reserva]);

    $confirmation_message = 'La reserva ha sido eliminada exitosamente.';
}

// Manejo de agregar y actualizar tarifas
if (isset($_POST['add_or_update_tarifa'])) {
    $evento = $_POST['evento'];
    $tarifa = $_POST['tarifa'];
    $evento_id = $_POST['evento_id'] ?? null;

    if ($evento_id) {
        $stmt = $pdo->prepare('UPDATE tarifas SET evento = ?, tarifa = ? WHERE id = ?');
        $stmt->execute([$evento, $tarifa, $evento_id]);
        $tarifa_message = 'Tarifa actualizada exitosamente.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO tarifas (evento, tarifa) VALUES (?, ?)');
        $stmt->execute([$evento, $tarifa]);
        $tarifa_message = 'Nueva tarifa agregada exitosamente.';
    }
}

// Manejo de la eliminación de tarifas
if (isset($_POST['delete_tarifa'])) {
    $tarifa_id = $_POST['tarifa_id'];
    $stmt = $pdo->prepare('DELETE FROM tarifas WHERE id = ?');
    $stmt->execute([$tarifa_id]);
    $tarifa_message = 'Tarifa eliminada exitosamente.';
}

// Recuperar pagos pendientes con nombre del usuario
$pago_stmt = $pdo->prepare('
    SELECT r.id_reserva, r.evento, r.fecha, u.nombre AS usuario_nombre
    FROM reservas r
    JOIN usuarios u ON r.usuario_id = u.id
    WHERE r.confirmado = 0
');
$pago_stmt->execute();
$pagos_pendientes = $pago_stmt->fetchAll(PDO::FETCH_ASSOC);

// Recuperar tarifas existentes
$tarifa_stmt = $pdo->prepare('SELECT * FROM tarifas');
$tarifa_stmt->execute();
$tarifas = $tarifa_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Funcionario</title>
    <!-- Bootstrap CSS -->
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../html/img/logo.png">
    <style>
        body {
            display: flex;
            height: 100vh;
            background-color: #f8f9fa;
            color: #343a40;
        }
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            height: 100%;
            position: fixed;
        }
        .sidebar a {
            color: white;
            padding: 10px;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            flex-grow: 1;
        }
        .form-container {
            display: none;
        }
        .form-section {
            margin-top: 20px;
        }
        .profile-container {
            margin-top: 20px;
            text-align: center;
        }
        .profile-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #343a40;
        }
        .profile-container h4 {
            margin-top: 10px;
            font-size: 18px;
            font-weight: 600;
        }
        .profile-container p {
            font-size: 14px;
            color: #6c757d;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h3 class="text-center text-white">Funcionario Panel</h3>
        <div class="profile-container">
            <img src="../html/img/admin.png" alt="Foto de Perfil" class="profile-img">
            <h4>Bienvenido, <?php echo htmlspecialchars($funcionario_nombre); ?>!</h4>
            <p class="text-muted">Funcionario</p>
        </div>
        <a href="#" onclick="toggleForm('confirmPaymentForm')">Confirmar Pagos</a>
        <a href="#" onclick="toggleForm('tarifaForm')">Agregar/Actualizar Tarifas</a>
        <form method="post" class="mt-3">
            <button type="submit" name="logout" class="btn btn-danger w-100">Cerrar Sesión</button>
        </form>
    </div>

    <div class="main-content">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Bienvenido al Panel del Funcionario</h5>
            </div>
            <div class="card-body">

                <!-- Mensajes de Confirmación -->
                <?php if (isset($confirmation_message)): ?>
                    <div class="alert alert-success">
                        <?php echo $confirmation_message; ?>
                    </div>
                <?php endif; ?>

                <!-- Sección de Confirmar Pagos -->
                <div id="confirmPaymentForm" class="form-container">
                    <h5>Confirmar Pagos</h5>
                    <?php if (count($pagos_pendientes) > 0): ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID Reserva</th>
                                    <th>Evento</th>
                                    <th>Fecha</th>
                                    <th>Nombre del Usuario</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagos_pendientes as $pago): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($pago['id_reserva']); ?></td>
                                        <td><?php echo htmlspecialchars($pago['evento']); ?></td>
                                        <td><?php echo htmlspecialchars($pago['fecha']); ?></td>
                                        <td><?php echo htmlspecialchars($pago['usuario_nombre']); ?></td>
                                        <td>
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="id_reserva" value="<?php echo htmlspecialchars($pago['id_reserva']); ?>">
                                                <button type="submit" name="confirm_payment" class="btn btn-success">Confirmar Pago</button>
                                            </form>
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="id_reserva" value="<?php echo htmlspecialchars($pago['id_reserva']); ?>">
                                                <button type="submit" name="delete_reserva" class="btn btn-danger">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No hay pagos pendientes.</p>
                    <?php endif; ?>
                </div>

                <!-- Sección de Agregar/Actualizar Tarifas -->
                <div id="tarifaForm" class="form-container">
                    <h5>Agregar/Actualizar Tarifas</h5>
                    <form method="post">
                        <div class="mb-3">
                            <label for="evento" class="form-label">Evento:</label>
                            <input type="text" class="form-control" id="evento" name="evento" required>
                        </div>
                        <div class="mb-3">
                            <label for="tarifa" class="form-label">Tarifa:</label>
                            <input type="number" class="form-control" id="tarifa" name="tarifa" required>
                        </div>
                        <input type="hidden" name="evento_id" id="evento_id">
                        <button type="submit" name="add_or_update_tarifa" class="btn btn-primary">Guardar Tarifa</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="jquery-3.5.1.min.js"></script>
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para mostrar u ocultar formularios
        function toggleForm(formId) {
            var form = document.getElementById(formId);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>

</body>
</html>

