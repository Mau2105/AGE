<?php 
session_start();
require 'config.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'usuario') {
    header('Location: login.php');
    exit();
}

// Obtener datos del usuario
$usuario_id = $_SESSION['usuario_id'];
$usuario_stmt = $pdo->prepare('SELECT nombre, correo FROM usuarios WHERE id = ?');
$usuario_stmt->execute([$usuario_id]);
$usuario = $usuario_stmt->fetch(PDO::FETCH_ASSOC);

// Obtener tarifas de eventos disponibles
$eventos_stmt = $pdo->prepare('SELECT * FROM tarifas');
$eventos_stmt->execute();
$eventos = $eventos_stmt->fetchAll(PDO::FETCH_ASSOC);

// Variables para manejar la reserva
$mostrar_pago = false;
$confirmation_message = '';
$mensaje_error = '';
$evento_seleccionado = '';

// Si hay un evento preseleccionado en la sesión, mostrar el formulario de pago
if (isset($_SESSION['evento_seleccionado'])) {
    $evento_seleccionado = $_SESSION['evento_seleccionado'];
    $mostrar_pago = true;
    unset($_SESSION['evento_seleccionado']);
}

// Manejo de la selección de evento
if (isset($_POST['select_evento'])) {
    $evento_seleccionado = $_POST['evento'];
    if ($evento_seleccionado) {
        $mostrar_pago = true;
    } else {
        $mensaje_error = "Debes seleccionar un evento.";
    }
}

// Manejo de la confirmación de pago
if (isset($_POST['confirm_payment'])) {
    $evento = $_POST['evento'];
    $fecha = $_POST['fecha'];
    $invitados = $_POST['invitados'];
    $tipo_pago = isset($_POST['tipo_pago']) ? $_POST['tipo_pago'] : 'presencial';
    
    if ($invitados > 150) {
        $mensaje_error = "El número de invitados supera el límite de 150. Por favor, póngase en contacto con nosotros.";
    } else {
        $fecha_reserva = date('Y-m-d H:i:s');
        $evento_stmt = $pdo->prepare('SELECT tarifa FROM tarifas WHERE evento = ?');
        $evento_stmt->execute([$evento]);
        $tarifa = $evento_stmt->fetch(PDO::FETCH_ASSOC)['tarifa'];

        // Determinar el estado de confirmación basado en el tipo de pago
        $confirmado = ($tipo_pago === 'online' && isset($_FILES['comprobante'])) ? 1 : 0;

        // Procesar la imagen del comprobante si es pago online
        $ruta_comprobante = null;
        if ($tipo_pago === 'online' && isset($_FILES['comprobante'])) {
            $archivo = $_FILES['comprobante'];
            $nombre_archivo = uniqid() . '_' . $archivo['name'];
            
            // Asegúrate de que la carpeta comprobantes existe
            if (!file_exists('comprobantes')) {
                mkdir('comprobantes', 0777, true);
            }
            
            $ruta_destino = 'comprobantes/' . $nombre_archivo;
            
            if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                $ruta_comprobante = $ruta_destino;
            }
        }

        // Insertar reserva en la base de datos
        $stmt = $pdo->prepare('INSERT INTO reservas (usuario_id, evento, fecha, invitados, confirmado, fecha_reserva, comprobante) 
                             VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$usuario_id, $evento, $fecha, $invitados, $confirmado, $fecha_reserva, $ruta_comprobante]);

        $confirmation_message = ($confirmado == 1) ? 
            'Tu reserva ha sido confirmada.' : 
            'Tu reserva ha sido registrada como pendiente de pago presencial.';
    }
}

// Verificar reservas confirmadas - Inicializar el array
$reservas_confirmadas = [];

// Obtener las reservas del usuario
$reserva_stmt = $pdo->prepare(
    'SELECT r.id_reserva, r.evento, r.fecha, r.invitados, r.confirmado, r.fecha_reserva 
     FROM reservas r
     WHERE r.usuario_id = ?'
);
$reserva_stmt->execute([$usuario_id]);
$reservas_confirmadas = $reserva_stmt->fetchAll(PDO::FETCH_ASSOC);

// Eliminar una reserva si el usuario lo desea
if (isset($_GET['eliminar_reserva'])) {
    $id_reserva = $_GET['eliminar_reserva'];
    $delete_stmt = $pdo->prepare('DELETE FROM reservas WHERE id_reserva = ? AND usuario_id = ?');
    $delete_stmt->execute([$id_reserva, $usuario_id]);
    header('Location: usuario.php');
    exit();
}

// Eliminar automáticamente las reservas cuyo tiempo ha expirado
foreach ($reservas_confirmadas as $reserva) {
    $fecha_reserva = strtotime($reserva['fecha_reserva']);
    $tiempo_transcurrido = time() - $fecha_reserva;
    $tiempo_limite = 48 * 60 * 60; // 48 horas en segundos

    if ($tiempo_transcurrido > $tiempo_limite && !$reserva['confirmado']) {
        $delete_stmt = $pdo->prepare('DELETE FROM reservas WHERE id_reserva = ?');
        $delete_stmt->execute([$reserva['id_reserva']]);
    }
}

// Cerrar sesión
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interfaz de Usuario</title>
    <link rel="shortcut icon" href="../html/img/logo.png">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        background-color: #f4f6f9;
        font-family: 'Arial', sans-serif;
        color: #333;
    }

    .navbar {
        background-color: #007bff;
    }

    .navbar-brand {
        font-size: 1.8rem;
        font-weight: bold;
    }

    .navbar-dark .navbar-nav .nav-link {
        color: #ffffff;
    }

    .navbar-dark .navbar-nav .nav-link:hover {
        color: #ffd700;
    }

    .card-header {
        background-color: #007bff;
        color: #fff;
    }

    .card-body {
        background-color: #fff;
        padding: 2rem;
    }

    .btn-primary,
    .btn-danger,
    .btn-success {
        padding: 10px 20px;
        font-size: 1.1rem;
        border-radius: 25px;
    }

    .btn-primary {
        background-color: #007bff;
        border: none;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }

    .btn-danger {
        background-color: #dc3545;
        border: none;
    }

    .btn-danger:hover {
        background-color: #c82333;
    }

    .btn-success {
        background-color: #28a745;
        border: none;
    }

    .btn-success:hover {
        background-color: #218838;
    }

    .form-label {
        font-weight: 600;
    }

    .form-control {
        border-radius: 25px;
    }

    .table {
        border-collapse: separate;
        border-spacing: 0 10px;
    }

    .table th,
    .table td {
        padding: 15px;
        text-align: center;
        vertical-align: middle;
    }

    .table th {
        background-color: #007bff;
        color: #fff;
        font-weight: bold;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #f8f9fa;
    }

    .alert {
        padding: 1rem;
        font-size: 1.2rem;
        border-radius: 10px;
        margin-top: 1rem;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
    }

    .map-container {
        margin-top: 2rem;
        text-align: center;
    }

    .map-container iframe {
        border-radius: 10px;
    }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../html/cumpleaños.html">Reservas de Eventos</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <form method="post" class="d-inline">
                            <button type="submit" name="logout" class="btn btn-danger">Cerrar Sesión</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="card mb-4 shadow-sm">
            <div class="card-header">
                <h5 class="card-title">Bienvenido, <?php echo htmlspecialchars($usuario['nombre']); ?>!</h5>
            </div>
            <div class="card-body">
                <?php if ($mensaje_error): ?>
                <div class="alert alert-danger">
                    <?php echo $mensaje_error; ?>
                </div>
                <?php endif; ?>

                <?php if ($confirmation_message): ?>
                <div class="alert alert-success">
                    <?php echo $confirmation_message; ?>
                </div>
                <?php endif; ?>

                <?php if ($mostrar_pago): ?>
                <div class="card shadow-sm mt-4">
                    <div class="card-header">
                        <h5 class="card-title">Formulario de Pago</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data" id="mainForm">
                            <div class="mb-3">
                                <label for="evento" class="form-label">Evento:</label>
                                <input type="text" class="form-control" id="evento" name="evento"
                                    value="<?php echo htmlspecialchars($evento_seleccionado); ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="fecha" class="form-label">Fecha de Reserva:</label>
                                <input type="date" class="form-control" id="fecha" name="fecha" required
                                    min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="invitados" class="form-label">Número de Invitados:</label>
                                <input type="number" class="form-control" id="invitados" name="invitados" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tipo de Pago:</label>
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#pagoOnlineModal">
                                        Pago Online
                                    </button>
                                    <button type="submit" name="confirm_payment" class="btn btn-secondary"
                                        onclick="document.getElementById('tipo_pago').value='presencial'">
                                        Pago Presencial
                                    </button>
                                </div>
                                <input type="hidden" name="tipo_pago" id="tipo_pago" value="">
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal para Pago Online -->
                <div class="modal fade" id="pagoOnlineModal" tabindex="-1" aria-labelledby="pagoOnlineModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="pagoOnlineModalLabel">Cargar Comprobante de Pago</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="formPagoOnline" method="post" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="comprobante" class="form-label">Seleccionar Comprobante:</label>
                                        <input type="file" class="form-control" id="comprobante" name="comprobante"
                                            accept="image/*" required>
                                    </div>
                                    <input type="hidden" name="tipo_pago" value="online">
                                    <input type="hidden" name="evento"
                                        value="<?php echo htmlspecialchars($evento_seleccionado); ?>">
                                    <input type="hidden" name="fecha" id="fecha_modal">
                                    <input type="hidden" name="invitados" id="invitados_modal">
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" name="confirm_payment" class="btn btn-primary">Confirmar
                                            Pago</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title">Mis Reservas</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($reservas_confirmadas)): ?>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Evento</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="reservas-body">
                        <?php foreach ($reservas_confirmadas as $reserva): 
                                $fecha_reserva = strtotime($reserva['fecha_reserva']);
                                $tiempo_transcurrido = time() - $fecha_reserva;
                                $tiempo_limite = 48 * 60 * 60; // 48 horas en segundos
                                $tiempo_restante = $tiempo_limite - $tiempo_transcurrido;

                                // Si está confirmado
                                if ($reserva['confirmado'] == 1) {
                                    $estado = "Confirmado";
                                    $estado_clase = "text-success";
                                } else {
                                    // Si está pendiente
                                    $estado = "Pendiente";
                                    $estado_clase = "text-info";

                                    // Si la reserva está dentro del límite de 48 horas
                                    if ($tiempo_restante > 0) {
                                        $estado .= " - <span id='countdown-".$reserva['id_reserva']."'>" . gmdate("H:i:s", $tiempo_restante) . "</span>";
                                    } else {
                                        $estado = "Eliminada";
                                        $estado_clase = "text-danger";
                                    }
                                }
                            ?>
                        <tr class="reservation-row">
                            <td><?php echo htmlspecialchars($reserva['evento']); ?></td>
                            <td><?php echo htmlspecialchars($reserva['fecha']); ?></td>
                            <td class="<?php echo $estado_clase; ?>" style="position: relative;">
                                <?php echo $estado; ?>
                            </td>
                            <td>
                                <?php if (!$reserva['confirmado']): ?>
                                <a href="?eliminar_reserva=<?php echo htmlspecialchars($reserva['id_reserva']); ?>"
                                    class="btn btn-danger">Eliminar</a>
                                <?php else: ?>
                                <a href="factura.php?id_reserva=<?php echo htmlspecialchars($reserva['id_reserva']); ?>"
                                    class="btn btn-success">Imprimir Factura</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>No tienes reservas confirmadas.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="map-container">
        <h4>Ubicación del Evento</h4>
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3984.934462007163!2d-75.29413222565144!3d2.8352209550034937!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e3b6d83b49354f7%3A0xb4c45a494299aafa!2sClub%20Campestre%20de%20Neiva!5e0!3m2!1ses!2sco!4v1731097091886!5m2!1ses!2sco"
            width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
    </div>

    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
  <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
    // Código para sincronizar formularios
    document.addEventListener('DOMContentLoaded', function() {
        // Obtener el modal
        const pagoOnlineModal = document.getElementById('pagoOnlineModal');
        
        // Actualizar los campos ocultos del modal cuando se abre
        pagoOnlineModal.addEventListener('show.bs.modal', function() {
            // Obtener valores del formulario principal
            const fecha = document.getElementById('fecha').value;
            const invitados = document.getElementById('invitados').value;
            
            // Actualizar campos ocultos en el modal
            document.getElementById('fecha_modal').value = fecha;
            document.getElementById('invitados_modal').value = invitados;
        });
        
        // Validar que se hayan ingresado los datos antes de abrir el modal
        document.querySelector('[data-bs-target="#pagoOnlineModal"]').addEventListener('click', function(e) {
            const fecha = document.getElementById('fecha').value;
            const invitados = document.getElementById('invitados').value;
            
            if (!fecha || !invitados) {
                e.preventDefault();
                alert('Por favor, complete la fecha y el número de invitados antes de proceder al pago online.');
            }
        });
    });

    // Actualizar el tiempo de cuenta regresiva en vivo
    setInterval(function() {
        document.querySelectorAll('[id^="countdown-"]').forEach(function(element) {
            let countdownText = element.innerText;
            let timeParts = countdownText.split(':');
            let hours = parseInt(timeParts[0]);
            let minutes = parseInt(timeParts[1]);
            let seconds = parseInt(timeParts[2]);

            if (hours > 0 || minutes > 0 || seconds > 0) {
                if (seconds > 0) {
                    seconds--;
                } else {
                    seconds = 59;
                    if (minutes > 0) {
                        minutes--;
                    } else {
                        minutes = 59;
                        hours--;
                    }
                }
                element.innerText = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2,
                    '0') + ':' + String(seconds).padStart(2, '0');
            } else {
                // El tiempo ha expirado
                let row = element.closest('tr');
                row.querySelector('.btn-danger').click(); // Eliminar automáticamente el evento
            }
        });
    }, 1000);
    </script>
</body>

</html>