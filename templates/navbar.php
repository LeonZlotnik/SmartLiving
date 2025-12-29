<?php
 include("../sesion.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
 <!-- Bootstrap 5 CSS -->
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap 5 JS (requiere Popper también) -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
<script src="https://use.fontawesome.com/releases/v5.12.1/js/all.js" data-auto-replace-svg="nest"></script>
</head>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <a class="navbar-brand" href="../index.php">
    <img src="../img/Logo_corpoh9_mini.png" alt="">
  </a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
        data-bs-target="#navbarSupportedContent" 
        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
  <span class="navbar-toggler-icon"></span>
</button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
      <li class="nav-item">
        <?php 
        //echo "<p class='nav-link'>".$_SESSION['user']."</p>"
        ?>
      </li>
      <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarScrollingDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Menu
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
          <li><a class="dropdown-item" href="main.php">Menu Prncipal</a></li>
            <li><a class="dropdown-item" href="control_admin.php">Accesos</a></li>
            <li><a class="dropdown-item" href="formulario.php">Conciliador</a></li>
            <li><a class="dropdown-item" href="base_mantenimiento.php">Tabla de Ingresos</a></li>
            <li><a class="dropdown-item" href="base_egresos.php">Tabla de Egresos</a></li>
            <li><a class="dropdown-item" href="dashboard.php">Graficas</a></li>
          </ul>
      <!--<li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarScrollingDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Categorías
        </a>
        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="crearadmin.php">Accesos</a></li>
            <li><a class="dropdown-item" href="formulario.php">Conciliador</a></li>
            <li><a class="dropdown-item" href="base_mantenimiento.php">Tabla de Ingresos</a></li>
            <li><a class="dropdown-item" href="base_egresos.php">Tabla de Egresos</a></li>
            <li><a class="dropdown-item" href="dashboard.php">Graficas</a></li>
        </ul>
      </li>-->
    </ul>
    <form class="d-flex">
    <?php
      if(isset($_SESSION['username'])){
        echo "<a href='logout_admin.php?salir' class='btn btn-outline-light'>Salir</a>";
 }
?>
    </form>
  </div>
  
</nav>

</html>