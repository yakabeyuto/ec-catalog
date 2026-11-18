<form action="favorite-toggle.php" method="post">
    <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
    <button><?= $isFavorite ? '♥ お気に入り済み（解除）' : '♡ お気に入りに登録' ?></button>
</form>
