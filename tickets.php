<?php require_once 'includes/header.php'; ?>

<?php
// 1. ADAPTACIÓN DE SEGURIDAD (Se mantiene igual)
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

// 2. LÓGICA DE BORRADO (Se mantiene igual)
if ( isset($_POST['id_del']) && $_SESSION['userId'] == 1 ) {
    $id_a_borrar = (int)$_POST['id_del']; 
    if ($id_a_borrar > 0) {
        $sql_delete = "DELETE FROM ticket WHERE id = $id_a_borrar";
        if ($connect->query($sql_delete) === TRUE) {
            echo '
                <div class="alert alert-info alert-dismissible fade in col-sm-3 animated bounceInDown" role="alert" style="position:fixed; top:70px; right:10px; z-index:1000;"> 
                    <button type...></button>
                    <h4 class="text-center">TICKET ELIMINADO</h4>
                    <p class="text-center">El ticket fue eliminado con éxito.</p>
                </div>
            ';
        } else {
            echo '
                <div class="alert alert-danger alert-dismissible fade in col-sm-3 animated bounceInDown" role="alert" style="position:fixed; top:70px; right:10px; z-index:1000;"> 
                    <button type...></button>
                    <h4 class="text-center">OCURRIÓ UN ERROR</h4>
                    <p class="text-center">No hemos podido eliminar el ticket.</p>
                </div>
            '; 
        }
    }
}

// 3. CONTADORES (Se mantiene igual)
$sql_counts = "SELECT 
                    COUNT(*) AS total_all,
                    SUM(CASE WHEN estado_ticket = 'Pendiente' THEN 1 ELSE 0 END) AS total_pend,
                    SUM(CASE WHEN estado_ticket = 'En proceso' THEN 1 ELSE 0 END) AS total_proceso,
                    SUM(CASE WHEN estado_ticket = 'Resuelto' THEN 1 ELSE 0 END) AS total_res
                FROM ticket";
$result_counts = $connect->query($sql_counts);
$counts = $result_counts->fetch_assoc();
$num_total_all = $counts['total_all'];
$num_total_pend = $counts['total_pend'];
$num_total_proceso = $counts['total_proceso'];
$num_total_res = $counts['total_res'];
?>

<div class="container">
  <div class="row">
    <div class="col-sm-2">
      <img src="./img/msj.png" alt="Image" class="img-responsive animated tada">
    </div>
    <div class="col-sm-10">
      <p class="lead text-info">Bienvenido. Aquí se muestran todas las tareas y tickets internos del sistema.</p>
    </div>
  </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <ul class="nav nav-pills nav-justified">
                <?php $ticket_view = isset($_GET['ticket']) ? $_GET['ticket'] : 'all'; ?>
                <li class="<?php echo ($ticket_view == 'all') ? 'active' : ''; ?>">
                    <a href="tickets.php?ticket=all"><i class="fa fa-list"></i>&nbsp;&nbsp;Todos los tickets&nbsp;&nbsp;<span class="badge"><?php echo $num_total_all; ?></span></a>
                </li>
                <li class="<?php echo ($ticket_view == 'pending') ? 'active' : ''; ?>">
                    <a href="tickets.php?ticket=pending"><i class="fa fa-envelope"></i>&nbsp;&nbsp;Tickets pendientes&nbsp;&nbsp;<span class="badge"><?php echo $num_total_pend; ?></span></a>
                </li>
                <li class="<?php echo ($ticket_view == 'process') ? 'active' : ''; ?>">
                    <a href="tickets.php?ticket=process"><i class="fa fa-folder-open"></i>&nbsp;&nbsp;Tickets en proceso&nbsp;&nbsp;<span class="badge"><?php echo $num_total_proceso; ?></span></a>
                </li>
                <li class="<?php echo ($ticket_view == 'resolved') ? 'active' : ''; ?>">
                    <a href="tickets.php?ticket=resolved"><i class="fa fa-thumbs-o-up"></i>&nbsp;&nbsp;Tickets resueltos&nbsp;&nbsp;<span class="badge"><?php echo $num_total_res; ?></span></a>
                </li>
            </ul>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <?php
                    // 4. LÓGICA DE LISTADO Y PAGINACIÓN (¡¡MODIFICADA!!)
                    
                    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
                    $regpagina = 15;
                    $inicio = ($pagina > 1) ? (($pagina * $regpagina) - $regpagina) : 0;

                    // ¡CAMBIO IMPORTANTE! Hacemos el JOIN con la tabla users
                    $sql_base = "FROM ticket
                                 LEFT JOIN users ON ticket.user_id_asignado = users.user_id";
                    
                    if ($ticket_view == "pending") {
                        $sql_base .= " WHERE ticket.estado_ticket='Pendiente'";
                    } elseif ($ticket_view == "process") {
                        $sql_base .= " WHERE ticket.estado_ticket='En proceso'";
                    } elseif ($ticket_view == "resolved") {
                        $sql_base .= " WHERE ticket.estado_ticket='Resuelto'";
                    }

                    // Consulta para total de registros
                    $total_registros_query = $connect->query("SELECT COUNT(ticket.id) AS total $sql_base");
                    $total_registros = $total_registros_query->fetch_assoc()['total'];
                    $numeropaginas = ceil($total_registros / $regpagina);

                    // Consulta para obtener los registros de la página actual
                    // Seleccionamos campos específicos para evitar ambigüedad (ej. ticket.id)
                    $sql_listado = "SELECT ticket.*, users.username 
                                    $sql_base 
                                    ORDER BY ticket.id DESC
                                    LIMIT $inicio, $regpagina";
                    
                    $resultado_listado = $connect->query($sql_listado);

                    if ($resultado_listado && $resultado_listado->num_rows > 0):
                ?>
                <table class="table table-hover table-striped table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">Serie</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Asignado a</th>
                            <th class="text-center">Departamento</th>
                            <th class="text-center">Asunto</th> <th class="text-center">Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $ct = $inicio + 1;
                            while ($row = $resultado_listado->fetch_assoc()): 
                        ?>
                        <tr>
                            <td class="text-center"><?php echo $ct; ?></td>
                            <td class="text-center"><?php echo $row['fecha']; ?></td>
                            <td class="text-center"><?php echo $row['serie']; ?></td>
                            <td class="text-center"><?php echo $row['estado_ticket']; ?></td>
                            
                            <td class="text-center">
                                <?php 
                                    if ($row['username']) {
                                        echo $row['username'];
                                    } else {
                                        // Muestra 'Nadie' si user_id_asignado es NULL
                                        echo '<span class="text-muted">Nadie</span>';
                                    }
                                ?>
                            </td>
                            
                            <td class="text-center"><?php echo $row['departamento']; ?></td>
                            <td class="text-center"><?php echo $row['asunto']; ?></td> <td class="text-center">
                                <a href="./lib/pdf.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success" target="_blank"><i class="fa fa-print" aria-hidden="true"></i></a>
                                <a href="ticket_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                                
                                <?php if (isset($_SESSION['userId']) && $_SESSION['userId'] == 1): ?>
                                    <form action="tickets.php?ticket=<?php echo $ticket_view; ?>" method="POST" style="display: inline-block;">
                                        <input type="hidden" name="id_del" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                            $ct++;
                            endwhile; 
                        ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <h2 class="text-center">No hay tickets registrados en esta categoría</h2>
                <?php endif; ?>
            </div>
            
            <?php 
                // 6. PAGINACIÓN (Se mantiene igual)
                if ($numeropaginas >= 1):
            ?>
            <nav aria-label="Page navigation" class="text-center">
                <ul class="pagination">
                    <?php if ($pagina == 1): ?>
                        <li class="disabled">
                            <a aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="tickets.php?ticket=<?php echo $ticket_view; ?>&pagina=<?php echo $pagina - 1; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php
                        for ($i = 1; $i <= $numeropaginas; $i++) {
                            if ($pagina == $i) {
                                echo '<li class="active"><a href="tickets.php?ticket=' . $ticket_view . '&pagina=' . $i . '">' . $i . '</a></li>';
                            } else {
                                echo '<li><a href="tickets.php?ticket=' . $ticket_view . '&pagina=' . $i . '">' . $i . '</a></li>';
                            }
                        }
                    ?>
                    
                    <?php if ($pagina == $numeropaginas): ?>
                        <li class="disabled">
                            <a aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="tickets.php?ticket=<?php echo $ticket_view; ?>&pagina=<?php echo $pagina + 1; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div><?php require_once 'includes/footer.php'; ?>