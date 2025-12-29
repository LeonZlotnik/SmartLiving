<?php
require_once('z_connect.php');

if(isset($_POST['create'])){

    $name = $_POST['name'];
    $email = $_POST['email'];
    $pw = $_POST['password'];
    $resident =$_POST['num_residencia'];
    $phone = $_POST['phone'];
    $condo = $_POST['residencial'];


    if (empty($name) || empty($pw)) {
        echo "<script>alert('Usuario o contraseña vacíos');</script>";
        exit();
    }

    // Encriptar la contraseña
    $pw_hashed = password_hash($pw, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (name, email, password, num_residencia, phone, residencial) VALUES ('$name','$email', '$pw','$resident','$phone','$condo');";
    $result = mysqli_query($conn, $sql) or die ("Error en query: $sql - " . mysqli_error($conn));

    if($result){
        header('Location:templates/index.html');
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
</head>
<body>
<?php require_once('navbar_login.php')?>
<br>
<h2 class="text-center">Registrar</h2>
<br>
<section class="container">
<form method="POST" enctype="multipart/form-data">
  <div class="form-row">
    <label for="inputAddress2">Nombre:</label>
    <input type="text" name="name"  class="form-control" id="inputAddress2" placeholder="Inrtoduce nombre completo">
  </div>
  <br>
  <div class="form-row">
    <label for="inputAddress2">Correo:</label>
    <input type="text" name="email"  class="form-control" id="inputAddress2" placeholder="Introduce tu email">
  </div>
  <br>
  <div class="form-row">
    <label for="inputAddress2">Telefono:</label>
    <input type="tel" name="phone"  class="form-control" id="inputAddress2" placeholder="Introduce tu telefono">
  </div>
  <br>
  <div class="form-row">
    <label for="inputAddress2">Casa/Departamento:</label>
    <input type="text" name="num_residencia"  class="form-control" id="inputAddress2" placeholder="Introduce numero de casa o departament">
  </div>
  <br>
  <div class="form-row">
    <label for="inputAddress2">Nombre de Residencial:</label>
    <input type="text" name="residencial"  class="form-control" id="inputAddress2" placeholder="Introduce e nombre de tu condominio">
  </div>
  <br>
  <div class="form-row">
    <label for="inputAddress2">Contraseña:</label>
    <input type="password" name="password"  class="form-control" id="inputAddress2" placeholder="Introduce contraseña">
  </div>
  <br>
  <div class="form-group">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" id="gridCheck">
      <label class="form-check-label" for="gridCheck">
        Check me out
      </label>
    </div>
  </div>
  <a href="login_user.php" class="btn btn-primary">Regresar</a>
  <input type="submit" name="create" class="btn btn-primary" value="Crear">
</form>
</section>
<br>
</body>
</html>