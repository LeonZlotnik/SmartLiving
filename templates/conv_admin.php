<?php
include("../sesion.php");
require_once("../z_connect.php");

if (empty($_SESSION['username'])) {
  header("Location:../login_admin.php");
  exit;
}

$adminEmail = $_SESSION['username'];
$adminAlias = "admin"; // porque los usuarios guardan receiver="admin"

$sql = "
SELECT
  CASE
    WHEN sender IN (?, ?) THEN receiver
    ELSE sender
  END AS other_user,
  MAX(`timestamp`) AS last_ts
FROM mensajes
WHERE sender IN (?, ?) OR receiver IN (?, ?)
GROUP BY other_user
ORDER BY last_ts DESC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
  die("Prepare failed: " . $conn->error);
}

$stmt->bind_param(
  "ssssss",
  $adminEmail, $adminAlias,
  $adminEmail, $adminAlias,
  $adminEmail, $adminAlias
);

if (!$stmt->execute()) {
  die("Execute failed: " . $stmt->error);
}

$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<body>
<?php require_once('navbar.php'); ?>

<div class="container">
  <h3>Conversaciones</h3>

  <?php if ($result && $result->num_rows === 0): ?>
    <div class="alert alert-warning">No hay conversaciones todavía.</div>
  <?php else: ?>
    <div class="list-group">
      <?php while($row = $result->fetch_assoc()): ?>
        <?php
          $other = $row['other_user'];
          // Evita mostrar al propio admin o el alias como conversación “consigo mismo”
          if ($other === $adminEmail || $other === $adminAlias) continue;
        ?>
        <a class="list-group-item list-group-item-action"
           href="mensajes.php?email=<?php echo urlencode($other); ?>">
          <?php echo htmlspecialchars($other); ?>
          <small class="d-block">Último: <?php echo htmlspecialchars($row['last_ts']); ?></small>
        </a>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>

</body>
</html>
<?php
$stmt->close();
$conn->close();
?>

