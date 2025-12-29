<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://use.fontawesome.com/releases/v5.12.1/js/all.js" data-auto-replace-svg="nest"></script> 
    <title>Tabla general</title>
</head>
<body>
<?php require_once('../z_connect.php')?>
<?php require_once('navbar.php')?>
<br>
<div class="col text-center">
      <a class="btn btn-primary btn-lg" href="main.php">Atras</a>
    </div>
    <br>

    <section class="container">
    <?php
    echo "<table class='table table-hover'>
    <thead>
        <tr>
            <th scope='col'>Residencial</th>
            <th scope='col'>Email</th>
            <th scope='col'>Departamento</th>
            <th scope='col'>Fecha de pago</th>
            <th scope='col'>Pago</th>
            <th scope='col'>Mantenimiento</th>
            <th scope='col'>Fecha limite del mes</th>
            <th scope='col'>Status</th>
            <th scope='col'>Penalización</th>
            <th scope='col'>Adeudo</th>
        </tr>
    </thead>";

    $sql = "SELECT * FROM conciliaciones";
    $result = $conn-> query($sql) or die ("error en query $sql".mysqli_error());

    if($result-> num_rows > 0) {
    
        while($row = mysqli_fetch_assoc($result)){
            echo "
            <tbody>
            <th scope='row'>".$row["residencial"]."</th>
            <td>".$row["email"]."</td>
            <td>".$row["clave"]."</td>
            <td>".$row["fecha_operacion"]."</td>
            <td>$".number_format($row["pago"], 2, '.', ',')."</td>
            <td>$".number_format($row["mantenimiento_final"], 2, ',', '.')."</td>
            <td>".$row["fecha_limite"]."</td>
            <td>".$row["status"]."</td>
            <td>$".number_format($row["fee"], 2, '.', ',')."</td>
            <td>$".number_format($row["adeudo"], 2, '.', ',')."</td>";
}
    echo "
        </tbody>
    </table>";
}
else {
    echo "<div class='alert alert-warning' role='alert'>
    No hay información por el momento.
          </div>";
}
    ?>
</body>
</html>