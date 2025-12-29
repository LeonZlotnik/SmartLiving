<?php

require_once('../z_connect.php');

//Edición de Producto

function calcularTotalConIVA($subtotal, $iva) {
    if ($iva === '' || $iva == 0) {
        $total = round($subtotal, 2);
        $iva = 0;
    } else {
        $total = round($subtotal * 1.16, 2);
        $iva = round($total - $subtotal, 2);
    }
    return [$total, $iva];
}

$id = 0;
$update = false;

if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $update = true;
    
    $query = "SELECT * FROM egresos WHERE expense_id = '$id'";
    $result = mysqli_query($conn, $query) or die("error en query $query " . mysqli_error($conn));

    if (mysqli_num_rows($result) == 1) {
        $row = $result->fetch_array();
        $fecha_tr = $row['fecha_transaccion'];
        $subtotal = $row['subtotal'];
        $iva = $row['iva'];
        $concepto = $row['concepto'];
        $beneficiario = $row['beneficiario'];
        $procedencia = $row['procedencia'];
    }
}

if (isset($_POST['update'])) {
    $new_fecha_tr = $_POST['fecha_transaccion'];
    $new_subtotal = floatval($_POST['subtotal'] === '' ? 0 : $_POST['subtotal']);
    $new_iva = floatval($_POST['iva'] === '' ? 0 : $_POST['iva']);
    $new_concepto = $_POST['concepto'];
    $new_beneficiario = $_POST['beneficiario'];
    $new_procedencia = $_POST['procedencia'];

    list($total, $new_iva) = calcularTotalConIVA($new_subtotal, $_POST['iva']);

    $mysql = "UPDATE egresos SET 
        fecha_transaccion = '$new_fecha_tr',
        subtotal = '$new_subtotal',
        iva = '$new_iva',
        concepto = '$new_concepto',
        beneficiario = '$new_beneficiario',
        procedencia = '$new_procedencia',
        total = '$total'
        WHERE expense_id = '$id'";

    $res = mysqli_query($conn, $mysql) or die("error en query $mysql " . mysqli_error($conn));

    if ($res) {
        mysqli_free_result($result);
        mysqli_close($conn);
        header('Location:base_egresos.php');
        exit(); // ← importante para detener ejecución
    } else {
        echo "<script>alert('Se ha generado un error');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Contacto</title>
    <link rel="stylesheet" type="text/css" href="admin_controll.css">
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

<br>
<section class="container">
<form method="POST" enctype="multipart/form-data">
  <input type="hidden" name="id_registro" value="<?php echo $id;?>">
  <div class="form-row">
    <div class="form-group col-md-4">
        <label for="inputAddress2" class="form-label text-light">Concepto:</label>
        <input type="text" class="form-control" id="inputAddress2" name="concepto" value="<?php echo $concepto;?>" placeholder="Inrtoduce nombre">
    </div>
    <br>
    <div class="form-group col-md-4">
            <label for="inputZip" class="form-label text-light">Beneficiario</label>
            <input type="text" class="form-control" id="inputAddress2" name="beneficiario" value="<?php echo $beneficiario;?>" id="inputZip" placeholder="000">
        </div>
    <br>
        <div class="form-group col-md-4">
            <label for="inputZip" class="form-label text-light">Procedencia</label>
            <input type="text" class="form-control" id="inputAddress2"  name="procedencia" value="<?php echo $procedencia;?>" id="inputZip" placeholder="000">
        </div>
    <br>
        <div class="form-group col-md-4">
            <label for="inputCity" class="form-label text-light">Subtotal:</label>
            <input type="number" step="0.01" min="0" max="100000" class="form-control"  name="subtotal" value="<?php echo $subtotal;?>" id="inputCity" placeholder="0.00">
        </div>
    <br>
        <div class="form-group col-md-2">
            <label for="inputCity" class="form-label text-light">IVA:</label>
            <input type="number" step="0.01" min="0" max="100000" class="form-control"  name="iva" value="<?php echo $iva;?>" id="inputCity" placeholder="0.00">
        </div>
    <br>
        <div class="form-group col-md-6">
            <label for="inputCity" class="form-label text-light">Fecha de Transacción :</label>
            <input type="date" class="form-control"  name="fecha_transaccion" value="<?php echo $fecha_tr;?>" id="inputCity" placeholder="DD/MM/YYYY">
        </div>  
    </div>

  <div class="form-group">
    <br>
  </div>
  <a href="base_egresos.php" class="btn btn-primary">Regresar</a>
  <?php if($update == true){?>
  <input class="btn btn-info" type="submit" name="update" value="Editar" id="gridCheck">
  <p><?php echo $mysql;?></p>
  <?php }else{?>
  <input class="btn btn-primary" type="submit" name="create" value="Crear" id="gridCheck">
  <?php }?>
</form>
</section>
<br>
</body>
</html>