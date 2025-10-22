<?php
require_once __DIR__.'/common/header.php';
if(!isset($_SESSION['admin_id'])){ header('Location: login.php'); exit; }
$errors=[]; $success='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    if(!$old || !$new) $errors[] = 'Both fields required.';
    else{
        $stmt = $pdo->prepare('SELECT password FROM admin WHERE id = ?'); $stmt->execute([$_SESSION['admin_id']]);
        $h = $stmt->fetchColumn();
        if(!password_verify($old,$h)) $errors[]='Old password incorrect.';
        else{ $nh = password_hash($new, PASSWORD_DEFAULT); $stmt = $pdo->prepare('UPDATE admin SET password=? WHERE id=?'); $stmt->execute([$nh,$_SESSION['admin_id']]); $success='Password updated.'; }
    }
}
?>

<div class="p-3 rounded app-card border border-gray-800">
  <?php foreach($errors as $e) echo '<div class="text-sm text-red-400">'.htmlspecialchars($e).'</div>'; ?>
  <?php if($success) echo '<div class="text-sm text-green-400">'.htmlspecialchars($success).'</div>'; ?>
  <form method="POST">
    <label class="text-xs">Old Password</label>
    <input name="old_password" type="password" class="w-full rounded p-2 mt-1 bg-gray-900 border border-gray-800" />
    <label class="text-xs mt-2">New Password</label>
    <input name="new_password" type="password" class="w-full rounded p-2 mt-1 bg-gray-900 border border-gray-800" />
    <button class="mt-3 w-full py-2 rounded bg-indigo-600">Update</button>
  </form>
</div>

<?php include __DIR__.'/common/bottom.php'; ?>
