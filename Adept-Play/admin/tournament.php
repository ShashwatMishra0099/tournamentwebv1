<?php
require_once __DIR__.'/common/header.php';
if(!isset($_SESSION['admin_id'])){ header('Location: login.php'); exit; }
$errors=[]; $success='';

// handle create or update
if($_SERVER['REQUEST_METHOD']==='POST'){
    $title = trim($_POST['title']??'');
    $game = trim($_POST['game_name']??'');
    $entry = floatval($_POST['entry_fee']??0);
    $prize = floatval($_POST['prize_pool']??0);
    $match_time = $_POST['match_time']??null;
    $commission = floatval($_POST['commission_percentage']??0);
    if(!$title||!$game||!$match_time) $errors[]='Title, Game and Match time required.';
    if(empty($errors)){
        if(isset($_POST['edit_id']) && intval($_POST['edit_id'])){
            $id = intval($_POST['edit_id']);
            $stmt = $pdo->prepare('UPDATE tournaments SET title=?,game_name=?,entry_fee=?,prize_pool=?,match_time=?,commission_percentage=? WHERE id=?');
            $stmt->execute([$title,$game,$entry,$prize,$match_time,$commission,$id]);
            $success='Tournament updated.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO tournaments (title,game_name,entry_fee,prize_pool,match_time,commission_percentage,created_at) VALUES (?,?,?,?,?,?,NOW())');
            $stmt->execute([$title,$game,$entry,$prize,$match_time,$commission]);
            $success='Tournament created.';
        }
    }
}

// handle delete
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $stmt= $pdo->prepare('DELETE FROM tournaments WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: tournament.php'); exit;
}

// load for edit
$edit = null;
if(isset($_GET['edit'])){
    $stmt = $pdo->prepare('SELECT * FROM tournaments WHERE id = ?'); $stmt->execute([intval($_GET['edit'])]); $edit = $stmt->fetch();
}

// list
$stmt = $pdo->query('SELECT * FROM tournaments ORDER BY match_time DESC'); $list = $stmt->fetchAll();
?>

<div class="grid grid-cols-1 gap-3">
  <div class="p-3 rounded app-card border border-gray-800">
    <h3 class="font-semibold mb-2"><?= $edit ? 'Edit Tournament' : 'Create Tournament' ?></h3>
    <?php foreach($errors as $e) echo '<div class="text-sm text-red-400">'.htmlspecialchars($e).'</div>'; ?>
    <?php if($success) echo '<div class="text-sm text-green-400">'.htmlspecialchars($success).'</div>'; ?>
    <form method="POST" class="mt-2">
      <?php if($edit): ?><input type="hidden" name="edit_id" value="<?=intval($edit['id'])?>"><?php endif; ?>
      <label class="text-xs">Title</label>
      <input name="title" value="<?=htmlspecialchars($edit['title']??'')?>" class="w-full rounded p-2 mt-1 bg-gray-900 border border-gray-800" />
      <label class="text-xs mt-2">Game Name</label>
      <input name="game_name" value="<?=htmlspecialchars($edit['game_name']??'')?>" class="w-full rounded p-2 mt-1 bg-gray-900 border border-gray-800" />
      <div class="grid grid-cols-3 gap-2 mt-2">
        <div>
          <label class="text-xs">Entry Fee</label>
          <input name="entry_fee" value="<?=htmlspecialchars($edit['entry_fee']??'0')?>" class="w-full rounded p-2 mt-1 bg-gray-900 border border-gray-800" />
        </div>
        <div>
          <label class="text-xs">Prize Pool</label>
          <input name="prize_pool" value="<?=htmlspecialchars($edit['prize_pool']??'0')?>" class="w-full rounded p-2 mt-1 bg-gray-900 border border-gray-800" />
        </div>
        <div>
          <label class="text-xs">Commission (%)</label>
          <input name="commission_percentage" value="<?=htmlspecialchars($edit['commission_percentage']??'0')?>" class="w-full rounded p-2 mt-1 bg-gray-900 border border-gray-800" />
        </div>
      </div>
      <label class="text-xs mt-2">Match Time</label>
      <input type="datetime-local" name="match_time" value="<?= $edit ? date('Y-m-d\TH:i', strtotime($edit['match_time'])) : '' ?>" class="w-full rounded p-2 mt-1 bg-gray-900 border border-gray-800" />
      <button class="mt-3 w-full py-2 rounded bg-indigo-600"><?= $edit ? 'Update' : 'Create' ?></button>
    </form>
  </div>

  <div class="p-3 rounded app-card border border-gray-800">
    <h3 class="font-semibold mb-2">All Tournaments</h3>
    <?php foreach($list as $t): ?>
      <div class="p-3 mb-2 rounded border border-gray-800 flex justify-between">
        <div>
          <div class="text-sm text-gray-400"><?=htmlspecialchars($t['game_name'])?></div>
          <div class="font-medium"><?=htmlspecialchars($t['title'])?></div>
        </div>
        <div class="text-right">
          <div class="text-xs text-gray-400">Prize</div>
          <div class="font-semibold"><?=rupee($t['prize_pool'])?></div>
          <div class="mt-2 flex gap-2">
            <a href="?edit=<?=$t['id']?>" class="px-2 py-1 rounded bg-gray-800">Edit</a>
            <a href="?delete=<?=$t['id']?>" onclick="return confirm('Delete?')" class="px-2 py-1 rounded bg-red-700">Delete</a>
            <a href="manage_tournament.php?id=<?=$t['id']?>" class="px-2 py-1 rounded bg-indigo-600">Manage</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include __DIR__.'/common/bottom.php'; ?>
