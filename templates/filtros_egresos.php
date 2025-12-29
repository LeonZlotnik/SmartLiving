<?php
require_once('../z_connect.php'); // Conexión a la base de datos

// Recibe y limpia inputs
$concepto     = isset($_POST['concepto']) ? trim($_POST['concepto']) : '';
$beneficiario = isset($_POST['beneficiario']) ? trim($_POST['beneficiario']) : '';
$procedencia  = isset($_POST['procedencia']) ? trim($_POST['procedencia']) : '';
$mes          = isset($_POST['mes_nombre']) ? trim($_POST['mes_nombre']) : '';
$ano          = isset($_POST['ano_nombre']) ? trim($_POST['ano_nombre']) : '';

// Construir la consulta base
$sql = "SELECT * FROM egresos WHERE 1=1";
$params = [];
$types = "";

// Añadir filtros solo si vienen datos
if ($concepto !== '') {
    $sql .= " AND concepto = ?";
    $params[] = $concepto;
    $types .= "s";
}

if ($beneficiario !== '') {
    $sql .= " AND beneficiario = ?";
    $params[] = $beneficiario;
    $types .= "s";
}

if ($procedencia !== '') {
    $sql .= " AND procedencia = ?";
    $params[] = $procedencia; // corregido
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

// Preparar y ejecutar la consulta
$stmt = $conn->prepare($sql);

// Vincular parámetros solo si hay filtros
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Inicializar total
$total_egresos = 0;

// Generar tabla HTML
echo "<div class='table-responsive'>";
echo "<table class='table table-dark table-hover align-middle text-center'>";
echo "<thead>
        <tr>
            <th scope='col'>Concepto</th>
            <th scope='col'>Beneficiario</th>
            <th scope='col'>Procedencia</th>
            <th scope='col'>Fecha de transacción</th>
            <th scope='col'>Subtotal</th>
            <th scope='col'>IVA</th>
            <th scope='col'>Total</th>
            <th scope='col'>Editar</th>
        </tr>
      </thead>";
echo "<tbody>";

if ($result->num_rows === 0) {
    echo "<tr><td colspan='8'>No se encontraron resultados.</td></tr>";
} else {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['concepto']) . "</td>";
        echo "<td>" . htmlspecialchars($row['beneficiario']) . "</td>";
        echo "<td>" . htmlspecialchars($row['procedencia']) . "</td>";
        echo "<td>" . htmlspecialchars($row['fecha_transaccion']) . "</td>";
        echo "<td>$" . number_format($row['subtotal'], 2, '.', ',') . "</td>";
        echo "<td>$" . number_format($row['iva'], 2, '.', ',') . "</td>";
        echo "<td>$" . number_format($row['total'], 2, '.', ',') . "</td>";
        echo "<td><a href='editaregreso.php?edit=" . $row["expense_id"] . "'><i class='fas fa-edit'></i></a></td>";
        echo "</tr>";

        $total_egresos += floatval($row['total']);
    }
}
echo "</tbody></table>";
echo "</div>";

// Mostrar resumen
$total_egresos_formateado = number_format($total_egresos, 2, '.', ',');

echo "
<div class='container mt-4'>
    <div class='row row-cols-1 row-cols-md-8 g-4 justify-content-center'>
        <div class='alert alert-info text-center h-100' style='margin:2%'>
            <strong>Total Gastado: $ $total_egresos_formateado</strong>
        </div>
    </div>
</div>
";

// Cerrar recursos
$stmt->close();
$conn->close();
?>
