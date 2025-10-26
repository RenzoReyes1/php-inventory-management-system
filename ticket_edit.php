<?php require_once 'includes/header.php'; ?>

<?php
// 1. ADAPTACIÓN DE SEGURIDAD (Cualquier usuario logueado)
if ( !isset($_SESSION['userId']) ) { 
    echo '
    <div class="container">
        <div class="row">
            <div class="col-sm-4">
                <img src="./img/Stop.png" alt="Image" class="img-responsive animated slideInDown"/><br>
                <img src="./img/SadTux.png" alt="Image" class="img-responsive"/>
            </div>
            <div class="col-sm-7 animated flip">
                <h1 class="text-danger">Acceso Denegado</h1>
                <h3 class="text-info text-center">Lo sentimos, debe iniciar sesión para ver esta página.</h3>
            </div>
            <div class="col-sm-1">&nbsp;</div>
        </div>
    </div>';
    require_once 'includes/footer.php';
    exit();
}

// --- SI LLEGAMOS AQUÍ, UN USUARIO (ADMIN O TÉCNICO) ESTÁ LOGUEADO ---

// 2. ADAPTACIÓN DE LÓGICA DE ACTUALIZACIÓN (POST) (¡¡MODIFICADA!!)
if (isset($_POST['id_edit']) && isset($_POST['solucion_ticket']) && isset($_POST['estado_ticket'])) {
    
    // Obtenemos y limpiamos los datos del formulario
    $id_edit = (int)$_POST['id_edit'];
    $estado_edit = $connect->real_escape_string($_POST['estado_ticket']);
    $solucion_edit = $connect->real_escape_string($_POST['solucion_ticket']);
    $radio_email = $_POST['optionsRadios'];
    
    // ¡NUEVO! Obtenemos el ID del técnico asignado
    // Si se selecciona "Sin Asignar", guardamos NULL
    $user_id_asignado_edit = $_POST['user_id_asignado'];
    if ($user_id_asignado_edit == 'NULL' || $user_id_asignado_edit == 0) {
        $sql_user_id = "NULL";
    } else {
        $sql_user_id = (int)$user_id_asignado_edit;
    }

    // Obtenemos el email y asunto ANTES de actualizar
    $sql_get_data = "SELECT email_cliente, asunto FROM ticket WHERE id = $id_edit";
    $data_result = $connect->query($sql_get_data);
    $ticket_data = $data_result->fetch_assoc();
    $email_cliente = $ticket_data['email_cliente'];
    $asunto_ticket = $ticket_data['asunto'];
    
    // ¡NUEVO! Creamos la consulta de actualización (con user_id_asignado)
    $sql_update = "UPDATE ticket 
                   SET 
                       estado_ticket = '$estado_edit', 
                       solucion = '$solucion_edit',
                       user_id_asignado = $sql_user_id
                   WHERE id = $id_edit";

    if ($connect->query($sql_update) === TRUE) {
        // Éxito
        echo '
        <div class="alert alert-info alert-dismissible fade in col-sm-3 animated bounceInDown" role="alert" style="position:fixed; top:70px; right:10px; z-index:1000;"> 
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <h4 class="text-center">TICKET Actualizado</h4>
            <p class="text-center">El ticket fue actualizado con éxito.</p>
        </div>
        ';

        // Lógica de envío de email (se mantiene igual)
        if ($radio_email == "option2") {
            $cabecera = "From: Soporte <soporte@tuempresa.com>";
            $mensaje_mail = "Estimado usuario, la solución a su problema ('$asunto_ticket') es la siguiente: \n\n$solucion_edit";
            $mensaje_mail = wordwrap($mensaje_mail, 70, "\r\n");
            mail($email_cliente, "Solución a su Ticket: " . $asunto_ticket, $mensaje_mail, $cabecera);
        }

    } else {
        // Error
        echo '
        <div class="alert alert-danger alert-dismissible fade in col-sm-3 animated bounceInDown" role="alert" style="position:fixed; top:70px; right:10px; z-index:1000;"> 
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <h4 class="text-center">OCURRIÓ UN ERROR</h4>
            <p class="text-center">No hemos podido actualizar el ticket: ' . $connect->error . '</p>
        </div>
        ';
    }
}

// 3. ADAPTACIÓN DE LÓGICA PARA MOSTRAR DATOS (GET)
$reg = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql_fetch = "SELECT * FROM ticket WHERE id = $id";
    $result_fetch = $connect->query($sql_fetch);

    if ($result_fetch && $result_fetch->num_rows > 0) {
        $reg = $result_fetch->fetch_assoc();
    }
}

// ¡NUEVO! Obtenemos la lista de TODOS los usuarios/técnicos
$users_list = array();
// ############################################
// ##        ¡AQUÍ ESTÁ LA CORRECCIÓN!       ##
// ############################################
$sql_get_users = "SELECT user_id, username FROM users"; // <-- Se quitó "WHERE status = 1"
// ############################################

$users_result = $connect->query($sql_get_users);
if ($users_result && $users_result->num_rows > 0) { // Se añadió la comprobación de $users_result
    while($user_row = $users_result->fetch_assoc()) {
        $users_list[] = $user_row;
    }
}

// Si no se encontró el ticket, mostramos un error
if ($reg == null) {
    echo '<div class="alert alert-danger text-center">Error: No se encontró el ticket con el ID especificado.</div>';
} else {
?>

<div class="container">
    <div class="row">
        <div class="col-sm-3">
            <img src="./img/Edit.png" alt="Image" class="img-responsive animated tada">
        </div>
        <div class="col-sm-9">
            <a href="tickets.php" class="btn btn-primary btn-sm pull-right"><i class="fa fa-reply"></i>&nbsp;&nbsp;Volver a la lista de Tickets</a>
        </div>
    </div>
</div>

<div class="container">
    <div class="col-sm-12">
        <form class="form-horizontal" role="form" action="ticket_edit.php?id=<?php echo $reg['id']; ?>" method="POST">
            <input type="hidden" name="id_edit" value="<?php echo $reg['id']; ?>">
            
            <div class="form-group">
                <label class="col-sm-2 control-label">Fecha</label>
                <div class='col-sm-10'>
                    <div class="input-group">
                        <input class="form-control" readonly type="text" name="fecha_ticket" value="<?php echo $reg['fecha']; ?>">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    </div>
                </div>
            </div>
        
            <div class="form-group">
                <label class="col-sm-2 control-label">Serie</label>
                <div class='col-sm-10'>
                    <div class="input-group">
                        <input class="form-control" readonly type="text" name="serie_ticket" value="<?php echo $reg['serie']; ?>">
                        <span class="input-group-addon"><i class="fa fa-barcode"></i></span>
                    </div>
                </div>
            </div>
        
            <div class="form-group">
                <label class="col-sm-2 control-label">Estado</label>
                <div class='col-sm-10'>
                    <div class="input-group">
                        <select class="form-control" name="estado_ticket">
                            <option value="<?php echo $reg['estado_ticket']; ?>"><?php echo $reg['estado_ticket']; ?> (Actual)</option>
                            <?php if($reg['estado_ticket'] != 'Pendiente') echo '<option value="Pendiente">Pendiente</option>'; ?>
                            <?php if($reg['estado_ticket'] != 'En proceso') echo '<option value="En proceso">En proceso</option>'; ?>
                            <?php if($reg['estado_ticket'] != 'Resuelto') echo '<option value="Resuelto">Resuelto</option>'; ?>
                        </select>
                        <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">Asignar a Técnico</label>
                <div class='col-sm-10'>
                    <div class="input-group">
                        <select class="form-control" name="user_id_asignado">
                            <option value="NULL">-- Sin Asignar --</option>
                            <?php 
                            foreach ($users_list as $user) {
                                $selected = ($reg['user_id_asignado'] == $user['user_id']) ? 'selected' : '';
                                echo "<option value='{$user['user_id']}' $selected>{$user['username']}</option>";
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
                        <input type="text" readonly class="form-control" name="departamento_ticket" value="<?php echo $reg['departamento']; ?>">
                        <span class="input-group-addon"><i class="fa fa-users"></i></span>
                    </div> 
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">Asunto</label>
                <div class="col-sm-10">
                    <div class='input-group'>
                        <input type="text" readonly class="form-control" name="asunto_ticket" value="<?php echo $reg['asunto']; ?>">
                        <span class="input-group-addon"><i class="fa fa-paperclip"></i></span>
                    </div> 
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">Mensaje</label>
                <div class="col-sm-10">
                    <textarea class="form-control" readonly rows="3" name="mensaje_ticket"><?php echo $reg['mensaje']; ?></textarea>
                </div>
            </div>
        
            <div class="form-group">
                <label class="col-sm-2 control-label">Solución</label>
                <div class="col-sm-10">
                    <textarea class="form-control" rows="3" name="solucion_ticket" required><?php echo $reg['solucion']; ?></textarea>
                </div>
            </div>
        
            <div class="row">
                <div class="col-sm-offset-5">
                    <div class="radio">
                        <label>
                            <input type="radio" name="optionsRadios" value="option1" checked>
                            No enviar solución al email del usuario
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="optionsRadios" value="option2">
                             Enviar solución al email del usuario
                        </label>
                    </div>
                </div>
            </div>
        
            <br>
        
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10 text-center">
                    <button type="submit" class="btn btn-info">Actualizar ticket</button>
                </div>
            </div>

        </form>
    </div></div><?php 
} // Cerramos el 'else' de si se encontró el ticket
require_once 'includes/footer.php'; 
?>