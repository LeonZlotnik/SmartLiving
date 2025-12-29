<?php
// filtrar.php
require_once('../z_connect.php'); // Aquí va la conexión a tu DB

// Recibe y limpia inputs
$residencial = isset($_POST['residencial']) ? trim($_POST['residencial']) : '';
$deparamento = isset($_POST['departamento']) ? trim($_POST['departamento']) : '';
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
$mes = isset($_POST['mes_nombre']) ? trim($_POST['mes_nombre']) : '';
$ano = isset($_POST['ano_nombre']) ? trim($_POST['ano_nombre']) : '';

// Construir la consulta base
$sql = "SELECT * FROM conciliaciones WHERE 1=1"; // 1=1 es truco para concatenar AND sin errores

$params = [];
$types = "";

// Añadir filtros solo si vienen datos
if ($residencial !== '') {
    $sql .= " AND residencial = ?";
    $params[] = $residencial;
    $types .= "s"; // s = string
}

if ($deparamento !== '') {
    $sql .= " AND departamento = ?";
    $params[] = $deparamento;
    $types .= "s";
}

if ($status !== '') {
    $sql .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

if ($mes !== '') {
    $sql .= " AND mes_nombre = ?";
    $params[] = $mes;
    $types .= "s";
}

if ($ano !== '') {
    $sql .= " AND ano_nombre = ?";
    $params[] = $ano;
    $types .= "s";
}

// Preparar la consulta
$stmt = $conn->prepare($sql);

if ($result->num_rows === 0) {
    echo "<div class='alert alert-warning text-center w-50 mx-auto'>No se encontraron resultados.</div>";
    exit;
}

// Vincular parámetros dinámicamente (solo si hay filtros)
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();

echo "<div class='table-responsive'>";
echo "<table class='table table-dark table-hover align-middle text-center'>";
echo "<thead>
<tr>
<th scope='col'>Residencial</th>
<th scope='col'>Email</th>
<th scope='col'>Departamento</th>
<th scope='col'>Fecha de pago</th>
<th scope='col'>Pago</th>
<th scope='col'>Mantenimiento</th>
<th scope='col'>Fecha limite de mes</th>
<th scope='col'>Status</th>
<th scope='col'>Penalización</th>
<th scope='col'>Adeudo</th>
<th scope='col'>Editar</th>
</tr>
</thead>";

echo "<tbody>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['residencial']) . "</td>";
    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
    echo "<td>" . htmlspecialchars($row['departamento']) . "</td>";
    echo "<td>" . htmlspecialchars($row['fecha_operacion']) . "</td>";
    echo "<td>$".number_format($row["pago"], 2, '.', ',')."</td>";
    echo "<td>$" . number_format($row['mantenimiento_final'], 2, '.', ',') . "</td>";
    echo "<td>" . htmlspecialchars($row['fecha_limite']) . "</td>";
    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
    echo "<td>$".number_format($row['fee'], 2, '.', ',')."</td>";
    echo "<td>$".number_format($row['adeudo'], 2, '.', ',')."</td>";
    echo "<td><a href='editarpago.php?edit=".$row["id_registro"]."'><i class='fas fa-edit'></i></a></td>";
    echo "</tr>";

    $total_pagado += floatval($row['pago']);
    $total_adeudo += floatval($row['adeudo']);
}
echo "</tbody>";
echo "</table>";
echo "</div>";

$total_pagado_formateado = number_format($total_pagado, 2, '.', ',');
$total_adeudo_formateado = number_format($total_adeudo, 2, '.', ',');


echo "<div class='container mt-4'>
        <div class='row justify-content-center g-4'>
            <div class='col-md-4'>
                <div class='alert alert-info text-center'>
                    <strong>Total Pagado: $ $total_pagado_formateado</strong>
                </div>
            </div>
            <div class='col-md-4'>
                <div class='alert alert-danger text-center'>
                    <strong>Total Adeudo: $ $total_adeudo_formateado</strong>
                </div>
            </div>
        </div>
      </div>";

$stmt->close();
$conn->close();
?>