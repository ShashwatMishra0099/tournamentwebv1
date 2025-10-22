<?php
require_once __DIR__.'/common/config.php';
if(!$pdo) die('Run install.php first.');
if(!is_logged()) header('Location: login.php');
$user = current_user($pdo);
$errors = [];
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(isset($_POST['update_profile'])){
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if(!$username || !$email) $errors[] = 'Username and Email required.';
        if(empty($errors)){
            $stmt = $pdo->prepare('UPDATE users SET username = ?, email = ? WHERE id = ?');
            $stmt->execute([$username,$email,$user['id']]);
            $success = 'Profile updated.';
            $user = current_user($pdo);
        }
    }
    if(isset($_POST['change_password'])){
        $old = $_POST['old_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        if(!$old || !$new) $errors[] = 'Both passwords required.';
        else{
            $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
            $stmt->execute([$user['id']]);
            $h = $stmt->fetchColumn();
            if(!password_verify($old,$h)) $errors[] = 'Old password incorrect.';
            else{
                $nh = password_hash($new, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                $stmt->execute([$nh,$user['id']]);
                $success = 'Password changed.';
            }
        }
    }
    if(isset($_POST['logout'])){
        session_unset(); session_destroy();
        header('Location: login.php'); exit;
    }
}

include __DIR__.'/common/header.php';
?>

<div class="app-card p-4 rounded">
  <?php if($errors): foreach($errors as $e) echo '<div class="mb-2 text-sm text-red-400">'.htmlspecialchars($e).'</div>'; endif; ?>
  <?php if($success) echo '<div class="mb-2 text-sm text-green-400">'.htmlspecialchars($success).'</div>'; ?>

  <form method="POST">
    <input type="hidden" name="update_profile" value="1">
    <label class="text-xs">Username</label>
    <input name="username" value="<?=htmlspecialchars($user['username'])?>" class="w-full rounded p-2 mt-1 bg-gray-900 border border-gray-800" />
    <label class="text-xs mt-2">Email</label>
    <input name="email" value="<?=htmlspecialchars($user['email'])?>" class="w-full rounded p-2 mt-1 bg-gray-900 border border-gray-800" />
    <button class="w-full mt-4 py-2 rounded bg-indigo-600">Update</button>
  </form>

  <hr class="my-4 border-gray-800" />

  <form method="POST">
    <input type="hidden" name="change_password" value="1">
    <label class="text-xs">Old Password</label>
    <input type="password" name="old_password" class="w-full rounded p-2 mt-1 bg-gray-900 border border-gray-800" />
    <label class="text-xs mt-2">New Password</label>
    <input type="password" name="new_password" class="w-full rounded p-2 mt-1 bg-gray-900 border border-gray-800" />
    <button class="w-full mt-4 py-2 rounded bg-gray-800">Change Password</button>
  </form>

  <form method="POST" class="mt-4">
    <input type="hidden" name="logout" value="1">
    <button class="w-full py-2 rounded bg-red-700">Logout</button>
  </form>
</div>

<?php include __DIR__.'/common/bottom.php'; ?>
