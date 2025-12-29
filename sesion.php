<?php
require_once('z_connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $adminEmail = $_POST["admin_email"];
    $password   = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM colaboradores WHERE admin_email=?");
    $stmt->bind_param("s", $adminEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {

        echo "<div style='color:white'>Usuario encontrado en BD</div>";
        echo "<pre>";
        echo "DEBUG: password ingresado: [$password]\n";
        echo "DEBUG: password en BD: [" . $row['password'] . "]\n";
        echo "DEBUG: password vacío? => " . (empty($row['password']) ? 'SI' : 'NO') . "\n";
        echo "DEBUG: comparación literal => " . ($password === $row['password'] ? 'IGUALES' : 'DISTINTOS') . "\n";
        echo "</pre>";

        // asegúrate de que password no sea null
        if (!empty($row['password']) && password_verify($password, $row['password'])) {
        //if ($password === $row['password']) {

            echo "<div style='color:green'>Password coincide</div>";
            echo "<hr>Rows encontradas: " . $result->num_rows . "<hr>";


            $_SESSION['username'] = trim($row["username"]);
            $_SESSION['admin_email'] = trim($row["admin_email"]);

            header('Location: templates/main.php');
            exit;

        } else {
            $msg = "<div class='alert alert-danger'>Contraseña incorrecta</div>";
        }

    } else {
        $msg = "<div class='alert alert-danger'>Usuario no encontrado</div>";
    }
}

if (isset($msg)) { echo $msg; } 
?>


