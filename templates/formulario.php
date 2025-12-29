<?php

include("../sesion.php"); // inicia sesión y verifica que el usuario esté logueado

$user = $_SESSION['username'];
$token = hash_hmac('sha256', $user, 'ZL@$nik199!');
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Conciliaciones</title>
        <style>
     body {
        background-color: #3c4552 !important;
        color: white;
        font-family: Arial, sans-serif;
    }
    label{
    color: white !important;
    }

    h2 {
    color: white !important;
    }
    .accordion-button {
      background-color: #212529 !important; /* fondo oscuro */
      color: white !important;             
    }
    .accordion-button:not(.collapsed) {
      color: white;
      background-color: #343a40; /* fondo más claro al abrir */
    }
    .accordion-body {
      background-color: #3c4552;
      color: white;
    }
    .accordion-button::after {
    filter: invert(1); /* hace el icono blanco */
  }
  .form-control {
    background-color: #495057;
    color: white;
    border: 1px solid #6c757d;
}
.form-control:focus {
    background-color: #495057;
    color: white;
    border-color: #adb5bd;
    box-shadow: none;
}
</style>
    </head>
    
<body>
<?php require_once('navbar.php')?>
    <div class="container">
    <div class="text-center mt-4">
      <a class="btn btn-info btn-lg px-5 py-2 fs-4" href="main.php">Atrás</a>
    </div>
</br>
        <div class="row justify-content-center">
<!-- Formulario conciliación -->
          <div class="accordion" id="accordionExample">
            <div class="accordion-item bg-dark text-white">
              <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                  Subir archivos para conciliación
                </button>
              </h2>
              <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                  <div class="col-8">
                    <form method="POST" enctype="multipart/form-data" action="http://127.0.0.1:5000/services">
                        <input type="hidden" name="action_type" value="conciliacion">
                        <input type="hidden" name="user" value="<?php echo $user; ?>">
                        <input type="hidden" name="token" value="<?php echo $token; ?>">
                        
                        <label class="btn btn-lg" for="pdf">Archivo PDF:</label>
                        <input type="file" name="pdf" class="form-control" accept=".pdf" required><br><br>
    
                        <label class="btn btn-lg" for="excel">Archivo Excel:</label>
                        <input type="file" name="excel" class="form-control" accept=".xlsx,.xls" required><br><br>
                        
                        <button class="btn btn-light btn-lg" type="submit">Subir y procesar</button>
                    </form>
                </div>
                </div>
              </div>
            </div>
<!-- Formulario egresos -->
            <div class="accordion-item bg-dark text-white">
              <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                  Subir archivos de egresos
                </button>
              </h2>
              <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                  <div class="col-8">
                    <form method="POST" enctype="multipart/form-data" action="http://127.0.0.1:5000/services">
                        <input type="hidden" name="action_type" value="egresos">
                        <input type="hidden" name="user" value="<?php echo $user; ?>">
                        <input type="hidden" name="token" value="<?php echo $token; ?>">
                        
                        <label class="btn btn-lg" for="excel">Archivo Excel:</label>
                        <input type="file" name="excel" class="form-control" accept=".xlsx,.xls" required><br><br>
                        
                        <button class="btn btn-light btn-lg" type="submit">Subir y procesar</button>
                    </form>
                </div>
                </div>
              </div>
            </div>
        </div>
    </div>
    
</body>
</html>
