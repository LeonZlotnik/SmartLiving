<?php
require_once('../z_connect.php');

if(isset($_POST['create'])){

    $usr = $_POST['username'];
    $pw = $_POST['password'];
    $mail = $_POST['admin_email'];

    if (empty($usr) || empty($pw) || empty($mail)) {
        echo "<script>alert('Usuario o contraseña vacíos');</script>";
        exit();
    }

    // Encriptar la contraseña
    $pw_hashed = password_hash($pw, PASSWORD_DEFAULT);

    $sql = "INSERT INTO colaboradores (username, password, admin_email) VALUES ('$usr','$pw_hashed', '$mail');";
    $result = mysqli_query($conn, $sql) or die ("Error en query: $sql - " . mysqli_error($conn));

    if($result){
        header('Location:control_admin.php');
        exit();
    } else {
        echo "<script>alert('Se ha generado un error');</script>";
    }

    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Creacion de admin</title>
    <link rel="stylesheet" type="text/css" href="admin_controll.css">
    <style>
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
</style>
</head>
<body>
<?php require_once('navbar.php')?>
<br>
<h2 class="text-center">Crear Administrador</h2>
<br>
<section class="container">
<form method="POST" enctype="multipart/form-data">
  <div class="form-row">
    <label for="inputAddress2">Usuario:</label>
    <input type="text" name="username"  class="form-control" id="inputAddress2" placeholder="Inrtoduce nombre de usuario">
  </div>
  <br>
  <div class="form-row">
    <label for="inputAddress2">Email:</label>
    <input type="email" name="admin_email"  class="form-control" id="inputAddress2" placeholder="Inrtoduce tu email">
  </div>
  <br>
  <div class="form-row">
    <label for="inputAddress2">Contraseña:</label>
    <input type="password" name="password"  class="form-control" id="inputAddress2" placeholder="Inrtoduce contraseña">
  </div>
  <br>
  <div class="form-group">
    <div class="form-check">
    </div>
  </div>
  <a href="control_admin.php" class="btn btn-info">Regresar</a>
  <input type="submit" name="create" class="btn btn-info" value="Crear">
</form>
</section>
<br>
</body>
</html>