<?php

require_once('../z_connect.php');

//Edición de Producto

function calcularStatusFeeAdeudo($pago, $mant, $fecha_op, $fecha_limite) {
    $fecha_op_ts = strtotime($fecha_op);
    $fecha_lim_ts = strtotime($fecha_limite);

    if (!$fecha_op_ts || !$fecha_lim_ts) {
        return ["error en fecha", 0, 0];
    }

    // Caso: No pagó nada
    if ($pago == 0) {
        $fee = round($mant * 0.25, 2);
        $adeudo = round($mant + $fee, 2);
        return ["no pagado", $fee, $adeudo];
    }

    // Caso: Pago parcial
    if ($pago < $mant - 0.01) {
        $fee = round($mant * 0.25, 2);
        $adeudo = round($mant + $fee - $pago, 2);
        return ["pago parcial", $fee, $adeudo];
    }

    // Caso: Pago completo, pero fuera de tiempo
    if ($pago >= $mant - 0.01 && $fecha_op_ts > $fecha_lim_ts) {
        $fee = round($mant * 0.25, 2);
        $adeudo = round($fee, 2);
        return ["pago atrasado", $fee, $adeudo];
    }

    // Caso: Pago completo y a tiempo
    return ["pagado", 0, 0];
}


$id = 0;
$update = false;

if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $update = true;
    
    $query = "SELECT * FROM conciliaciones WHERE id_registro = '$id'";
    $result = mysqli_query($conn, $query) or die("error en query $query " . mysqli_error($conn));

    if (mysqli_num_rows($result) == 1) {
        $row = $result->fetch_array();
        $fecha_op = $row['fecha_operacion'];
        $pago = $row['pago'];
        $depto = $row['departamento'];
        $resi = $row['residencial'];
        $limite = $row['fecha_limite'];
        $mant = $row['mantenimiento_final'];
    }
}

if (isset($_POST['update'])) {
    $new_fecha_op = $_POST['fecha_operacion'];
    $new_pago = floatval($_POST['pago'] === '' ? 0 : $_POST['pago']);
    $new_depto = $_POST['departamento'] === '' ? 0 : $_POST['departamento'];
    $new_resi = $_POST['residencial'];
    $new_limite = $_POST['fecha_limite'];
    $new_mant = floatval($_POST['mantenimiento_final'] === '' ? 0 : $_POST['mantenimiento_final']);

    list($new_status, $new_fee, $new_adeudo) = calcularStatusFeeAdeudo($new_pago, $new_mant, $new_fecha_op, $new_limite);

    $mysql = "UPDATE conciliaciones SET 
        fecha_operacion = '$new_fecha_op',
        pago = '$new_pago',
        departamento = '$new_depto',
        residencial = '$new_resi',
        fecha_limite = '$new_limite',
        mantenimiento_final = '$new_mant',
        fee = '$new_fee',
        adeudo = '$new_adeudo',
        status = '$new_status'
        WHERE id_registro = '$id'";

    $res = mysqli_query($conn, $mysql) or die("error en query $mysql " . mysqli_error($conn));

    if ($res) {
        mysqli_free_result($result);
        mysqli_close($conn);
        header('Location: base_mantenimiento.php');
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
    <br>
    <div class="form-group col-md-4">
        <label for="inputAddress2" class="form-label text-light">Residencial:</label>
        <input type="text" class="form-control" id="inputAddress2" name="residencial" value="<?php echo $resi;?>" placeholder="Inrtoduce nombre">
    </div>
    <br>
    <div class="form-group col-md-2">
            <label for="inputZip" class="form-label text-light">Departamento</label>
            <input type="number" min="0" max="1000" class="form-control"  name="departamento" value="<?php echo $depto;?>" id="inputZip" placeholder="000">
        </div>
    <br>
        <div class="form-group col-md-2">
            <label for="inputZip" class="form-label text-light">Mantenimiento</label>
            <input type="number" min="0" max="100000" class="form-control"  name="mantenimiento_final" value="<?php echo $mant;?>" id="inputZip" placeholder="000">
        </div>
    <br>
        <div class="form-group col-md-4">
            <label for="inputCity" class="form-label text-light">Pago:</label>
            <input type="number" step="0.01" min="0" max="100000" class="form-control"  name="pago" value="<?php echo $pago;?>" id="inputCity" placeholder="0.00">
        </div>
    <br>
        <div class="form-group col-md-6">
            <label for="inputCity" class="form-label text-light">Fecha de Operación:</label>
            <input type="date" class="form-control"  name="fecha_operacion" value="<?php echo $fecha_op;?>" id="inputCity" placeholder="DD/MM/YYYY">
        </div>  
    <br>
        <div class="form-group col-md-6">
            <label for="inputZip" class="form-label text-light">Fecha limite</label>
            <input type="date" class="form-control"  name="fecha_limite" value="<?php echo $limite;?>" id="inputZip" placeholder="DD/MM/YYYY">
        </div>
    </div>
    <br>
  <div class="form-group">
  </div>
  <a href="base_mantenimiento.php" class="btn btn-primary">Regresar</a>
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