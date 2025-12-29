<?php
include("../sesion.php");
require_once('../z_connect.php');

//Ingresos - Pagosd vs Adeudos

$sql_one_A = "SELECT SUM(pago) AS pago FROM conciliaciones";
$result_one_A = mysqli_query($conn, $sql_one_A) or die ("error en query $sql_one_A".mysqli_error());
$result_one_A_object = mysqli_fetch_object($result_one_A);

$sql_one_B = "SELECT SUM(adeudo) AS adeudo FROM conciliaciones";
$result_one_B = mysqli_query($conn, $sql_one_B) or die ("error en query $sql_one_B".mysqli_error());
$result_one_B_object = mysqli_fetch_object($result_one_B);

$intA = (int)$result_one_A_object->pago;
$intB = (int)$result_one_B_object->adeudo;

$datos_oneA = json_encode($intA);
$datos_oneB = json_encode($intB);

//Egresos vs Ingresos

$sql_two_A = "SELECT SUM(pago) AS ingresos FROM conciliaciones";
$result_two_A = mysqli_query($conn, $sql_two_A) or die ("error en query $sql_two_A".mysqli_error());
$result_two_A_object = mysqli_fetch_object($result_two_A);

$sql_two_B = "SELECT SUM(total) AS egresos FROM egresos";
$result_two_B = mysqli_query($conn, $sql_two_B) or die ("error en query $sql_two_B".mysqli_error());
$result_two_B_object = mysqli_fetch_object($result_two_B);

$numberA = (int)$result_two_A_object->ingresos;
$numberB = (int)$result_two_B_object->egresos;

$datos_twoA = json_encode($numberA);
$datos_twoB = json_encode($numberB);

// desglose de servicios 

$sql_three = "SELECT 
  beneficiario,
  mes_nombre,
  SUM(total) AS egresos
FROM egresos
GROUP BY beneficiario, mes_nombre
ORDER BY beneficiario, mes_nombre;";
$result_three = mysqli_query($conn, $sql_three) or die ("error en query $sql_three".mysqli_error());

$beneficiarios = [];
$egresos = [];

while ($row = mysqli_fetch_assoc($result_three)) {
  $beneficiarios[] = $row['beneficiario'];
  $egresos[] = (float)$row['egresos'];
}

$beneficiarios_json = json_encode($beneficiarios);
$egresos_json = json_encode($egresos);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
    body {
      background-color: #3c4552 !important;
      color: white;
      font-family: Arial, sans-serif;
    }
    label, small, h5 {
      color: white !important;
    }

    /* 🧩 Panel general */
    .panel {
      background-color: #2f3844;
      border-radius: 15px;
      padding: 30px;
      margin-bottom: 50px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }

    /* 🎯 Asegura que las gráficas ocupen el mismo espacio */
    .grafica-container {
      width: 100%;
      height: 400px;
      background-color: #394451;
      border-radius: 10px;
      padding: 15px;
    }

    /* 📱 Centrado y ajuste en pantallas pequeñas */
    @media (max-width: 768px) {
      .grafica-container {
        height: 350px;
      }
    }
  </style>
</head>
<body>
<?php require_once('navbar.php')?>

    <section class="container text-center my-5">
          
    <div class="text-center mt-5">
      <a class="btn btn-info btn-lg px-5 py-2 fs-4" href="main.php">Atrás</a>
    </div>
  </br>
    </section>
    <!-- Primera sección -->
  <section class="container-fluid">
    <div class="panel">
      <div class="row g-4">
        <div class="col-12 col-md-6">
          <div id="graficaDona" class="grafica-container"></div>
        </div>
        <div class="col-12 col-md-6">
          <div id="graficaPie" class="grafica-container"></div>
        </div>
      </div>
    </div>
  </section>
       
    <!-- Segunda sección -->
  <section class="container-fluid">
    <div class="panel">
      <div class="row g-4">
        <div class="col-12 col-md-12">
          <div id="graficaBarra" class="grafica-container"></div>
        </div>
      </div>
    </div>
  </section>

    <!-- Tercera sección -->
  <section class="container-fluid">
    <div class="panel">
      <div class="row g-4">
        <div class="col-12 col-md-6">
          <div id="graficaEscala" class="grafica-container"></div>
        </div>
        <div class="col-12 col-md-6">
          <div id="graficaCategorias" class="grafica-container"></div>
        </div>
      </div>
    </div>
  </section>
</body>
</html>

<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
<script src="graficas.js" type="text/javascript"></script>
<script type="text/javascript">

//Grafica Pie

let datos_oneA  = crearGraficaPie(<?php echo $datos_oneA ?>);
let datos_oneB  = crearGraficaPie(<?php echo $datos_oneB ?>);

var data_one = [{
  values: [datos_oneA , datos_oneB],
  labels: ['Pagos', 'Adeudos'],
  type: 'pie'
}];

var layout_one = {
  title: 'Distribución Pagos vs Adeudos ',
  font:{
    family: 'Raleway, sans-serif'
  },
  height: 400,
  width: 500
};

Plotly.newPlot('graficaPie', data_one, layout_one, {responsive: true});

// Gráfica Doughnut
let datos_twoA = crearGraficaDona(<?php echo $datos_twoA ?>);
let datos_twoB = crearGraficaDona(<?php echo $datos_twoB ?>);

var data_two = [{
  values: [datos_twoA, datos_twoB],
  labels: ['Ingresos', 'Egresos'],
  type: 'pie',
  hole: 0.4
}];

var layout_two = {
  title: 'Distribución Ingresos vs Egresos',
  font: { family: 'Raleway, sans-serif' },
  height: 400,
  width: 500, 
  marker: {
    colors: ['#00ffff', '#ff0060'] 
  }
};

Plotly.newPlot('graficaDona', data_two, layout_two,{responsive: true});

// Gráfica Barras
const beneficiarios = <?php echo $beneficiarios_json; ?>;
const egresos = <?php echo $egresos_json; ?>;

const data = [{
      x: beneficiarios,
      y: egresos,
      type: 'bar',
      marker: {
        color: ['#ff0060', '#4000ff', '#921d55', '#80ff00', '#001eff', '#ffa000']
      }
    }];

    const layout = {
  title: {
    text: 'Egresos por Beneficiario',
    font: { family: 'Raleway, sans-serif', size: 16, color: 'black' },
  },
  font: { family: 'Raleway, sans-serif', size: 11, color: 'black' },
  plot_bgcolor: '#FFFFFF',
  paper_bgcolor: '#FFFFFF',
  margin: { l: 60, r: 30, t: 60, b: 100 },
  xaxis: {
    title: { text: 'Beneficiario', font: { size: 12 } },
    tickangle: 0,
    automargin: true,
  },
  yaxis: {
    title: { text: 'Egresos (MXN)', font: { size: 12 } },
    automargin: true,
  },
  height: 420,
};

  Plotly.newPlot('graficaBarra', data, layout, {responsive: true});

</script>