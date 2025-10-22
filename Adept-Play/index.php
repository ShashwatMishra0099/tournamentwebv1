<?php
require_once __DIR__.'/common/config.php';
if(!$pdo){ die('Database not available. Run install.php.'); }

$messages = [];
// Handle Join Now submission
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_tournament'])){
    if(!is_logged()){
        $messages[] = ['type'=>'error','text'=>'Please login to join tournaments.'];
    } else {
        $tournament_id = intval($_POST['tournament_id']);
        $user_id = $_SESSION['user_id'];
        // fetch tournament
        $stmt = $pdo->prepare('SELECT * FROM tournaments WHERE id = ? AND status = "Upcoming"');
        $stmt->execute([$tournament_id]);
        $t = $stmt->fetch();
        if(!$t){ $messages[] = ['type'=>'error','text'=>'Tournament not found or not open.']; }
        else {
            // check already joined
            $stmt = $pdo->prepare('SELECT id FROM participants WHERE user_id = ? AND tournament_id = ?');
            $stmt->execute([$user_id,$tournament_id]);
            if($stmt->fetch()){
                $messages[] = ['type'=>'error','text'=>'You already joined this tournament.'];
            } else {
                // check balance
                $stmt = $pdo->prepare('SELECT wallet_balance FROM users WHERE id = ?');
                $stmt->execute([$user_id]);
                $u = $stmt->fetch();
                $balance = $u ? (float)$u['wallet_balance'] : 0.0;
                $fee = (float)$t['entry_fee'];
                if($balance < $fee){
                    $messages[] = ['type'=>'error','text'=>'Insufficient balance.'];
                } else {
                    // deduct
                    $pdo->beginTransaction();
                    try{
                        $stmt = $pdo->prepare('UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?');
                        $stmt->execute([$fee,$user_id]);
                        $stmt = $pdo->prepare('INSERT INTO transactions (user_id, amount, type, description, created_at) VALUES (?,?,?,?,NOW())');
                        $stmt->execute([$user_id, $fee, 'debit', 'Entry fee for tournament #'.$tournament_id]);
                        $stmt = $pdo->prepare('INSERT INTO participants (user_id, tournament_id) VALUES (?,?)');
                        $stmt->execute([$user_id,$tournament_id]);
                        $pdo->commit();
                        $messages[] = ['type'=>'success','text'=>'Successfully joined the tournament.'];
                    } catch(Exception $e){ $pdo->rollBack(); $messages[] = ['type'=>'error','text'=>'Failed to join.']; }
                }
            }
        }
    }
}

// fetch upcoming tournaments
$stmt = $pdo->query('SELECT * FROM tournaments WHERE status = "Upcoming" ORDER BY match_time ASC');
$tournaments = $stmt->fetchAll();

include __DIR__.'/common/header.php';
?>

<div class="mb-4">
  <div class="flex items-center justify-between">
    <h2 class="text-xl font-semibold">Upcoming Tournaments</h2>
    <a href="login.php" class="text-sm text-gray-400">Refresh</a>
  </div>
</div>

<?php foreach($messages as $m): ?>
  <div class="mb-3 p-3 rounded <?php echo $m['type']=='success' ? 'bg-green-900/30 border border-green-700' : 'bg-red-900/30 border border-red-700'; ?>">
    <?php echo htmlspecialchars($m['text']); ?>
  </div>
<?php endforeach; ?>

<div class="grid gap-3">
<?php if(empty($tournaments)): ?>
  <div class="p-4 rounded app-card">No upcoming tournaments.</div>
<?php endif; ?>

<?php foreach($tournaments as $t): ?>
  <div class="p-4 rounded-lg app-card border border-gray-800">
    <div class="flex justify-between items-start">
      <div>
        <div class="text-sm text-gray-400"><?=htmlspecialchars($t['game_name'])?></div>
        <div class="font-semibold text-lg"><?=htmlspecialchars($t['title'])?></div>
        <div class="text-xs text-gray-400 mt-1">Match: <?=htmlspecialchars(date('d M, Y H:i', strtotime($t['match_time'])))?></div>
      </div>
      <div class="text-right">
        <div class="text-sm">Entry</div>
        <div class="font-medium"><?=rupee($t['entry_fee'])?></div>
        <div class="text-sm text-gray-400 mt-2">Prize: <?=rupee($t['prize_pool'])?></div>
      </div>
    </div>

    <div class="mt-3 flex gap-2">
      <form method="POST" class="flex-1">
        <input type="hidden" name="tournament_id" value="<?=intval($t['id'])?>">
        <button name="join_tournament" class="w-full py-2 rounded bg-indigo-600">Join Now</button>
      </form>
      <a href="manage_tournament.php?id=<?=intval($t['id'])?>" class="py-2 px-3 rounded bg-gray-800 border border-gray-700">Details</a>
    </div>
  </div>
<?php endforeach; ?>
</div>

<?php include __DIR__.'/common/bottom.php'; ?>
