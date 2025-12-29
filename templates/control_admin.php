<?php 
include("../sesion.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Control de Administradores</title>
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
    <?php require_once('../z_connect.php')?>
    <br>
    
    <div class="col text-center">
        <a class="btn btn-info btn-lg" href="main.php">Atras</a>
      <a href="crearadmin.php" class="btn btn-default btn btn-info btn-lg">Crear Administrador</a>
    </div>
    <br>
    
    <section class="container">
    <?php 

        echo "
        <div class='table-responsive'>
            <table class='table table-dark table-hover align-middle text-center'>
                    <thead>
                        <tr>
                            <th scope='col'>#</th>
                            <th scope='col'>Usuario</th>
                            <th scope='col'>Contraseña</th>
                            <th scope='col'>Eliminar</th>
                        </tr>
                    </thead>";

                    $sql = "SELECT * FROM colaboradores";
                    $result = $conn-> query($sql) or die ("error en query $sql".mysqli_error());

                    if($result-> num_rows > 0) {
                    
                        while($row = mysqli_fetch_assoc($result)){
                            echo "
                            <tbody>
                            <th scope='row'>".$row["user_id"]."</th>
                            <td>".$row["username"]."</td>
                            <td>".$row["password"]."</td>
                            <td><a href='control_admin.php?delete=".$row["user_id"]."'><i class='fas fa-trash-alt'></i></a></td>";
                }
                    echo "
                        </tbody>
                    </table>
                </div>";
                }
                else {
                    echo "<div class='alert alert-warning' role='alert'>
                    No hay información por el momento.
                          </div>";
                }

                if(isset($_GET['delete'])){
                    $id = $_GET['delete'];
                    $conn->query("DELETE FROM colaboradores WHERE user_id = '$id'");
                }
    
                $conn-> close();
    
    ?>
    </section>
</body>
</html>