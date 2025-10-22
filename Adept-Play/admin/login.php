<?php
require_once __DIR__.'/../common/config.php';
$errors = [];
if(isset($_GET['logout'])){ session_unset(); session_destroy(); header('Location: login.php'); exit; }
if($_SERVER['REQUEST_METHOD']==='POST'){
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if(!$username || !$password) $errors[] = 'Provide username & password.';
    if(empty($errors) && $pdo){
        $stmt = $pdo->prepare('SELECT id,password FROM admin WHERE username = ?');
        $stmt->execute([$username]);
        $a = $stmt->fetch();
        if(!$a || !password_verify($password, $a['password'])) $errors[] = 'Invalid admin credentials.';
        else {
            $_SESSION['admin_id'] = $a['id'];
            header('Location: index.php'); exit;
        }
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center">
  <div class="max-w-md w-full p-6 rounded-2xl bg-gradient-to-b from-gray-800 to-gray-900 border border-gray-700">
    <h2 class="text-xl font-semibold mb-4">Admin Login</h2>
    <?php foreach($errors as $e) echo '<div class="mb-2 text-red-400">'.htmlspecialchars($e).'</div>'; ?>
    <form method="POST">
      <label class="text-xs">Username</label>
      <input name="username" class="w-full rounded p-2 mt-1 bg-gray-900 border border-gray-800" />
      <label class="text-xs mt-2">Password</label>
      <input type="password" name="password" class="w-full rounded p-2 mt-1 bg-gray-900 border border-gray-800" />
      <button class="w-full mt-4 py-2 rounded bg-indigo-600">Login</button>
    </form>
  </div>
</body>
</html>
