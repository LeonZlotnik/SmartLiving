<?php 
include("../sesion.php");

$adminEmail = $_SESSION['username'];

$receiverEmail = $_GET['email'] ?? '';
if ($receiverEmail === '') {
  header("Location: mensajeria_admin_home.php");
  exit;
}

$secret = 'ZL@$nik199!';

$token = hash_hmac('sha256', $adminEmail, $secret);
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

<iframe 
   src="http://127.0.0.1:8888/msn-admin?admin_email=<?php echo urlencode($adminEmail); ?>&email=<?php echo urlencode($receiverEmail); ?>&token=<?php echo $token; ?>"
   allow="camera; microphone"
   title="Chat en tiempo real">
</iframe>
</div>

</body>
</html>
