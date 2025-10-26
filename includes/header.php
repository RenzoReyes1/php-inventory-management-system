<?php require_once 'php_action/core.php'; ?>

<!DOCTYPE html>
<html>
<head>

  <title>Sistema de Gestión</title> <link rel="stylesheet" href="assests/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assests/bootstrap/css/bootstrap-theme.min.css">
  <link rel="stylesheet" href="assests/font-awesome/css/font-awesome.min.css">

  <link rel="stylesheet" href="custom/css/custom.css">

  <link rel="stylesheet" href="assests/plugins/datatables/jquery.dataTables.min.css">

  <link rel="stylesheet" href="assests/plugins/fileinput/css/fileinput.min.css">

  <script src="assests/jquery/jquery.min.js"></script>
  <link rel="stylesheet" href="assests/jquery-ui/jquery-ui.min.css">
  <script src="assests/jquery-ui/jquery-ui.min.js"></script>

  <script src="assests/bootstrap/js/bootstrap.min.js"></script>

</head>
<body>


  <nav class="navbar navbar-default navbar-static-top">
    <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Alternar navegación</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">      

      <ul class="nav navbar-nav navbar-right">        

        <li id="navDashboard"><a href="index.php"><i class="glyphicon glyphicon-list-alt"></i>  Panel</a></li>        
        
        <?php if(isset($_SESSION['userId']) && $_SESSION['userId']==1) { ?>
        <li id="navBrand"><a href="brand.php"><i class="glyphicon glyphicon-btc"></i>  Marca</a></li>        
        <?php } ?>
        
        <?php if(isset($_SESSION['userId']) && $_SESSION['userId']==1) { ?>
        <li id="navCategories"><a href="categories.php"> <i class="glyphicon glyphicon-th-list"></i> Categoría</a></li>        
        <?php } ?>
        
        <?php if(isset($_SESSION['userId']) && $_SESSION['userId']==1) { ?>
        <li id="navProduct"><a href="product.php"> <i class="glyphicon glyphicon-ruble"></i> Producto </a></li> 
        <?php } ?>
        
        <li class="dropdown" id="navOrder">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="glyphicon glyphicon-shopping-cart"></i> Pedidos <span class="caret"></span></a>
          <ul class="dropdown-menu">            
            <li id="topNavAddOrder"><a href="orders.php?o=add"> <i class="glyphicon glyphicon-plus"></i> Agregar pedidos</a></li>            
            <li id="topNavManageOrder"><a href="orders.php?o=manord"> <i class="glyphicon glyphicon-edit"></i> Gestionar pedidos</a></li>            
          </ul>
        </li> 

        <li id="navTickets"><a href="tickets.php"><i class="glyphicon glyphicon-tags"></i>  Tickets</a></li>
        <li id="navNewTicket"><a href="ticket_new.php"><i class="glyphicon glyphicon-plus"></i>  Nuevo Ticket</a></li>
        <?php  if(isset($_SESSION['userId']) && $_SESSION['userId']==1) { ?>
        <li id="navReport"><a href="report.php"> <i class="glyphicon glyphicon-check"></i> Informe </a></li>
        <?php } ?> 
        
        <?php  if(isset($_SESSION['userId']) && $_SESSION['userId']==1) { ?>
        <li id="importbrand"><a href="importbrand.php"> <i class="glyphicon glyphicon-check"></i> Marca de importación </a></li>
        <?php } ?>   
        
        <li class="dropdown" id="navSetting">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="glyphicon glyphicon-user"></i> <span class="caret"></span></a>
          <ul class="dropdown-menu">    
            <?php if(isset($_SESSION['userId']) && $_SESSION['userId']==1) { ?>
              <li id="topNavSetting"><a href="setting.php"> <i class="glyphicon glyphicon-wrench"></i> Configuración</a></li>
              <li id="topNavUser"><a href="user.php"> <i class="glyphicon glyphicon-wrench"></i> Agregar usuario</a></li>
              
              <?php } ?>           
            <li id="topNavLogout"><a href="logout.php"> <i class="glyphicon glyphicon-log-out"></i> Cerrar sesión</a></li>            
          </ul>
        </li>        
            
      </ul>
    </div></div></nav>

  <div class="container">