<?php
require_once __DIR__.'/common/config.php';
$errors = [];
$success = '';

// Handle signup
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action']==='signup'){
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if(!$username || !$email || !$password){ $errors[] = 'All fields are required for signup.'; }
    if($pdo){
        // check exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if($stmt->fetch()) $errors[] = 'Username or Email already exists.';
    }

    if(empty($errors)){
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username,email,password,wallet_balance,created_at) VALUES (?,?,?,?,NOW())');
        $stmt->execute([$username,$email,$hash,0]);
        $success = 'Signup successful. You can now login.';
    }
}

// Handle login
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action']==='login'){
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if(!$username || !$password) $errors[] = 'Please provide username and password.';

    if(empty($errors) && $pdo){
        $stmt = $pdo->prepare('SELECT id, password, is_blocked FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $u = $stmt->fetch();
        if(!$u || !password_verify($password, $u['password'])){
            $errors[] = 'Incorrect username or password.';
        } else if(isset($u['is_blocked']) && $u['is_blocked']){
            $errors[] = 'Your account has been blocked. Contact admin.';
        } else {
            $_SESSION['user_id'] = $u['id'];
            header('Location: index.php'); exit;
        }
    }
}

include __DIR__.'/common/header.php';
?>

<div class="app-card rounded-2xl p-4 mt-4">
  <div class="flex gap-2 bg-gray-800 rounded-xl p-1">
    <button id="tab-login" class="w-1/2 py-2 text-sm font-medium rounded-lg bg-transparent">Login</button>
    <button id="tab-signup" class="w-1/2 py-2 text-sm font-medium rounded-lg bg-transparent">Sign Up</button>
  </div>

  <?php if($errors): ?>
    <div class="mt-4 bg-red-900/30 border border-red-700 p-3 rounded">
      <?php foreach($errors as $e) echo '<div class="text-sm">'.htmlspecialchars($e).'</div>'; ?>
    </div>
  <?php endif; ?>
  <?php if($success): ?>
    <div class="mt-4 bg-green-900/30 border border-green-700 p-3 rounded text-sm"><?=htmlspecialchars($success)?></div>
  <?php endif; ?>

  <div id="panel-login" class="mt-4">
    <form method="POST">
      <input type="hidden" name="action" value="login">
      <label class="text-xs">Username</label>
      <input name="username" class="w-full rounded p-2 mt-1 bg-gray-900 border border-gray-800" />
      <label class="text-xs mt-2">Password</label>
      <input type="password" name="password" class="w-full rounded p-2 mt-1 bg-gray-900 border border-gray-800" />
      <button class="w-full mt-4 py-2 rounded bg-indigo-600">Login</button>
    </form>
  </div>

  <div id="panel-signup" class="hidden mt-4">
    <form method="POST">
      <input type="hidden" name="action" value="signup">
      <label class="text-xs">Username</label>
      <input name="username" class="w-full rounded p-2 mt-1 bg-gray-900 border border-gray-800" />
      <label class="text-xs mt-2">Email</label>
      <input name="email" class="w-full rounded p-2 mt-1 bg-gray-900 border border-gray-800" />
      <label class="text-xs mt-2">Password</label>
      <input type="password" name="password" class="w-full rounded p-2 mt-1 bg-gray-900 border border-gray-800" />
      <button class="w-full mt-4 py-2 rounded bg-indigo-600">Sign Up</button>
    </form>
  </div>

</div>

<script>
// basic tab toggling
const tabLogin = document.getElementById('tab-login');
const tabSignup = document.getElementById('tab-signup');
const panelLogin = document.getElementById('panel-login');
const panelSignup = document.getElementById('panel-signup');

function showLogin(){ tabLogin.classList.add('bg-indigo-600'); tabSignup.classList.remove('bg-indigo-600'); panelLogin.classList.remove('hidden'); panelSignup.classList.add('hidden'); }
function showSignup(){ tabSignup.classList.add('bg-indigo-600'); tabLogin.classList.remove('bg-indigo-600'); panelSignup.classList.remove('hidden'); panelLogin.classList.add('hidden'); }

tabLogin.addEventListener('click', showLogin);
tabSignup.addEventListener('click', showSignup);
// show login by default
showLogin();
</script>

<?php include __DIR__.'/common/bottom.php'; ?>
