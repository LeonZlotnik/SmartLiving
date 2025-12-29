<?php 
include("../sesion.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Base</title>
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
<div class="container">

    <div class="text-center mt-5">
      <a class="btn btn-info btn-lg px-5 py-2 fs-4" href="main.php">Atrás</a>
    </div>

<div class="row justify-content-center mt-5">
<div class="col-8">
<form action="filtros.php" class="row g-3" method="POST" id="filtro-form">
    <div class="col-auto">
        <label for="residencial">Residencial:</label>
        <input type="text" class="form-control" id="residencial" name="residencial">
    </div>
    <div class="col-auto">
        <label for="departamento">Departamento:</label>
        <input type="text" class="form-control" id="clave" name="departamento">
    </div>
    <div class="col-auto">
        <label for="status">Status:</label>
        <select name="status" class="form-control" id="status">
            <option value=""></option>
            <option value="pagado">Pagado</option>
            <option value="pago atrasado">Pago Atrasado</option>
            <option value="pago parcial">Pago Parcial</option>
            <option value="no pagado">No Pagado</option>
        </select>
     </div>
     <div class="col-auto">
        <label for="status">Mes:</label>
        <select name="mes_nombre" class="form-control" id="mes_nombre">
            <option value=""></option>
            <option value="Enero">Enero</option>
            <option value="Febrero">Febrero</option>
            <option value="Marzo">Marzo</option>
            <option value="Abril">Abril</option>
            <option value="Mayo">Mayo</option>
            <option value="Junio">Junio</option>
            <option value="Julio">Julio</option>
            <option value="Agosto">Agosto</option>
            <option value="Septiembre">Septiembre</option>
            <option value="Octubre">Octubre</option>
            <option value="Noviembre">Noviembre</option>
            <option value="Diciembre">Diciembre</option>
           
        </select>
     </div>
        <button type="submit" class="btn btn-outline-info btn-lg">Buscar</button>
</form>
</div>
</div>
</div>
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-10">
            <div id="resultado" class="mt-4 text-center"></div>
        </div>
    </div>
</div>
<script>
document.getElementById('filtro-form').addEventListener('submit', function(e) {
    e.preventDefault(); // Previene el envío normal del formulario

    const resultadoDiv = document.getElementById('resultado');
    resultadoDiv.innerHTML = `
        <div class="text-center my-3">
            <div class="spinner-border text-info" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Buscando...</p>
        </div>
    `;

    const formData = new FormData(this);

    fetch('filtros.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(html => {
        resultadoDiv.innerHTML = html; // Reemplaza el spinner por la tabla
    })
    .catch(error => {
        console.error('Error al cargar los datos:', error);
        resultadoDiv.innerHTML = `
            <div class="alert alert-danger" role="alert">
                Ocurrió un error al procesar la búsqueda. Intenta de nuevo.
            </div>
        `;
    });
});
</script>

</body>
</html>