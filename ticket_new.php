<?php require_once 'includes/header.php'; ?>

<?php
// Seguridad: Solo usuarios logueados pueden crear tickets
if ( !isset($_SESSION['userId']) ) { 
    echo '<div class="alert alert-danger text-center">Debe iniciar sesión para crear un ticket.</div>';
    require_once 'includes/footer.php';
    exit();
}

// Lógica para procesar el formulario cuando se envía (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Obtenemos y limpiamos datos
    $fecha_actual = date("Y-m-d H:i:s"); // Fecha y hora actuales
    $serie_ticket = "TKT-" . date("YmdHis"); // Generamos una serie única simple
    $estado_ticket = "Pendiente"; // Los tickets nuevos siempre son pendientes
    
    // ¡IMPORTANTE! Usamos el ID del usuario LOGUEADO como creador
    // Quitamos los campos nombre_usuario y email_cliente
    $user_id_creador = $_SESSION['userId']; 
    
    // Asignado a (Puede ser NULL si no se selecciona)
    $user_id_asignado = $_POST['user_id_asignado'];
    if ($user_id_asignado == 'NULL' || $user_id_asignado == 0) {
        $sql_user_id_asignado = "NULL";
    } else {
        $sql_user_id_asignado = (int)$user_id_asignado;
    }

    $departamento = $connect->real_escape_string($_POST['departamento_ticket']);
    $asunto = $connect->real_escape_string($_POST['asunto_ticket']);
    $mensaje = $connect->real_escape_string($_POST['mensaje_ticket']);
    $solucion = ""; // Solución vacía al crear

    // Creamos la consulta INSERT
    // Quitamos nombre_usuario y email_cliente
    $sql_insert = "INSERT INTO ticket 
                   (fecha, serie, estado_ticket, user_id_asignado, departamento, asunto, mensaje, solucion) 
                   VALUES 
                   ('$fecha_actual', '$serie_ticket', '$estado_ticket', $sql_user_id_asignado, '$departamento', '$asunto', '$mensaje', '$solucion')";

    if ($connect->query($sql_insert) === TRUE) {
        // Éxito
        echo '
        <div class="alert alert-success alert-dismissible fade in" role="alert"> 
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <h4 class="text-center">¡Ticket Creado!</h4>
            <p class="text-center">El nuevo ticket con serie <strong>' . $serie_ticket . '</strong> ha sido creado exitosamente.</p>
            <p class="text-center"><a href="tickets.php">Volver a la lista de tickets</a></p>
        </div>
        ';
    } else {
        // Error
        echo '
        <div class="alert alert-danger alert-dismissible fade in" role="alert"> 
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <h4 class="text-center">OCURRIÓ UN ERROR</h4>
            <p class="text-center">No se pudo crear el ticket: ' . $connect->error . '</p>
        </div>
        ';
    }

} else {
    // Si no es POST, mostramos el formulario
    
    // Obtenemos la lista de usuarios para el desplegable "Asignar a"
    $users_list = array();
    $sql_get_users = "SELECT user_id, username FROM users"; 
    $users_result = $connect->query($sql_get_users);
    if ($users_result && $users_result->num_rows > 0) { 
        while($user_row = $users_result->fetch_assoc()) {
            $users_list[] = $user_row;
        }
    }
?>

<div class="container">
    <div class="row">
        <div class="col-sm-3">
            <img src="./img/new_ticket.png" alt="Nuevo Ticket" class="img-responsive">
        </div>
        <div class="col-sm-9">
            <h3 class="text-info">Crear Nueva Tarea / Ticket Interno</h3>
            <p>Complete el formulario para registrar una nueva tarea o incidencia y asignarla a un técnico.</p>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-sm-12">
            <form class="form-horizontal" role="form" action="ticket_new.php" method="POST">
                
                <div class="form-group">
                    <label class="col-sm-2 control-label">Asignar a Técnico</label>
                    <div class='col-sm-10'>
                        <div class="input-group">
                            <select class="form-control" name="user_id_asignado">
                                <option value="NULL">-- Sin Asignar (Pendiente) --</option>
                                <?php 
                                foreach ($users_list as $user) {
                                    echo "<option value='{$user['user_id']}'>{$user['username']}</option>";
                                }
                                ?>
                            </select>
                            <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">Departamento</label>
                    <div class="col-sm-10">
                        <div class='input-group'>
                            <input type="text" class="form-control" name="departamento_ticket" placeholder="Ej: Soporte Técnico, Redes, Desarrollo" required>
                            <span class="input-group-addon"><i class="fa fa-users"></i></span>
                        </div> 
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">Asunto</label>
                    <div class="col-sm-10">
                        <div class='input-group'>
                            <input type="text" class="form-control" name="asunto_ticket" placeholder="Título breve de la tarea o problema" required>
                            <span class="input-group-addon"><i class="fa fa-paperclip"></i></span>
                        </div> 
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">Descripción / Mensaje</label>
                    <div class="col-sm-10">
                        <textarea class="form-control" rows="5" name="mensaje_ticket" placeholder="Describe detalladamente la tarea o el problema..." required></textarea>
                    </div>
                </div>
            
                <br>
            
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10 text-center">
                        <button type="submit" class="btn btn-info">Crear Ticket</button>
                        <a href="tickets.php" class="btn btn-default">Cancelar</a>
                    </div>
                </div>

            </form>
        </div></div></div><?php 
} // Fin del else (mostrar formulario)
require_once 'includes/footer.php'; 
?>