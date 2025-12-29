<?php 
  include("sesion.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Administradores</title>
    <link rel="stylesheet" type="text/css" href="background.css">
    <style>
    #gif{
      height: 200px;
      width: 300px;
    }
    body {
        background-color: #3c4552 !important;
        color: white;
        font-family: Arial, sans-serif;
    }
    label, small {
    color: white;
    }

    h2 {
    color: white !important;
    }
    .form-control {
    background-color: #495057;
    color: white;
    border: 1px solid #6c757d;
    }
  </style>
</head>
<body>
<?php require_once('navbar_login.php')?>
<br>
<h2 class="text-center">Acceso de Administradores</h2>
<br>
<div class="container">
</div>
<br>
<section class="container">
<?php if(!empty($msg)) echo $msg; ?>
<form action="" method="POST">
  <div class="form-group">
    <label for="exampleInputEmail1">Usuario</label>
    <input type="email" class="form-control" name="admin_email" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter email" required>
    <small id="emailHelp" class="form-text text-muted">Acceso unicamente para administradores</small>
  </div>
  <div class="form-group">
    <label for="exampleInputPassword1">Password</label>
    <input type="password" class="form-control" id="exampleInputPassword1" name="password" placeholder="Password" required>
  </div>
  <br>
  <input type="submit" class="btn btn-primary" name="login" value="Entrar">
</form>
</section>
<br>
<?php //require_once('footer.html')?>
</body>
</html>