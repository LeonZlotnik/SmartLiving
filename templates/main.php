<?php 
include("../sesion.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Main</title>
<style>
   body {
    background-color: #3c4552 !important;
    color: white;
    font-family: Arial, sans-serif;
}
</style>
</head>
<body>
<?php require_once('navbar.php')?>

<div class="container">
<br>
<?php 
    echo "<h5 class='h3' style='color: white;'><strong>Bienvenido al Control de Administradores {$_SESSION['username']}.</strong></h5>";
?>
<br>
<div class="card bg-dark text-white">
  <div class="card-header">
  <i class="fas fa-user-lock"></i> 
  </div>
  <div class="card-body">
    <h5 class="card-title">Control de Administradores</h5>
    <p class="card-text">Para tener más control los administradores tienen que tener un control.</p>
    <a href="control_admin.php" class="btn btn-info">Revisar</a>
  </div>
</div>
<br>



<div class="card bg-dark text-white">
  <div class="card-header">
  <i class="fas fa-comment-dollar"></i>
  </div>
  <div class="card-body">
    <h5 class="card-title">Conciliador de documentos</h5>
    <p class="card-text">Sube el PDF y el excel del condominio para conciliar los pagos</p>
    <a href="formulario.php" class="btn btn-info">Revisar</a>
  </div>
</div>
<br>

<div class="card bg-dark text-white">
  <div class="card-header">
  <i class="fas fa-address-book"></i>
  </div>
  <div class="card-body">
    <h5 class="card-title">Base de Datos Cuotas por Mantenimiento</h5>
    <p class="card-text">Revisa los pagos de los residenciales por departamento</p>
    <a href="base_mantenimiento.php" class="btn btn-info">Revisar</a>
  </div>
</div>
<br>

<div class="card bg-dark text-white">
  <div class="card-header">
  <i class="fas fa-handshake"></i>
  </div>
  <div class="card-body">
    <h5 class="card-title">Base de Datos Egresos de Servicios</h5>
    <p class="card-text">Revisa el historico de pagos del los condominios</p>
    <a href="base_egresos.php" class="btn btn-info">Revisar</a>
  </div>
</div>
  <br>

  <div class="card bg-dark text-white">
  <div class="card-header">
  <i class="fas fa-chart-area"></i>
  </div>
  <div class="card-body">
    <h5 class="card-title">Tablero Visual</h5>
    <p class="card-text">Revisa a detalle la información de condominios</p>
    <a href="dashboard.php" class="btn btn-info">Revisar</a>
  </div>
</div>
  <br>
  
  <div class="card bg-dark text-white">
  <div class="card-header">
  <i class="fas fa-comments"></i>
  </div>
  <div class="card-body">
    <h5 class="card-title">Canal de Mensajería</h5>
    <p class="card-text">Habla con los condominos en tiempo real</p>
    <a href="conv_admin.php" class="btn btn-info">Revisar</a>
  </div>
</div>
</div>
<br>


</div>
</body>
</html>