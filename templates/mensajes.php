<?php 
include("../sesion.php");

$adminEmail = $_SESSION['admin_email'] ?? ($_SESSION['username'] ?? '');

if ($adminEmail === '') {
  header("Location: mensajeria_admin_home.php");
  exit;
}

$receiverEmail = $_GET['email'] ?? '';
if ($receiverEmail === '') {
  header("Location: mensajeria_admin_home.php");
  exit;
}

$secret = 'ZL@$nik199!';

$token = hash_hmac('sha256', $adminEmail, $secret);

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
$port = 8080;
$query = http_build_query([
  'admin_email' => $adminEmail,
  'email' => $receiverEmail,
  'token' => $token,
]);
$chatUrl = sprintf('%s://%s:%d/msn-admin?%s', $scheme, $host, $port, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background-color: #3c4552 !important;
            color: white;
            font-family: Arial, sans-serif;
        }
        iframe {
            border: none;
            width: 100%;
            height: 80vh;
            border-radius: 10px;
            background: white;
        }
        .container {
            padding: 20px;
        }
    </style>
</head>
<body>
<?php require_once('navbar.php')?>

<div class="container">

<div class="text-center mt-5">
      <a class="btn btn-info btn-lg px-5 py-2 fs-4" href="main.php">Atrás</a>
    </div>
  </br>

<iframe 
   src=<?php echo htmlspecialchars($chatUrl, ENT_QUOTES, 'UTF-8');?>
   allow="camera; microphone"
   title="Chat en tiempo real">
</iframe>
</div>

</body>
</html>
