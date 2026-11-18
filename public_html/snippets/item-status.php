<?php
$isFavorite = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare(
        'SELECT 1 FROM favorites WHERE user_id = ? AND item_id = ?'
    );
    $stmt->execute([(int) $_SESSION['user_id'], $id]);
    $isFavorite = (bool) $stmt->fetchColumn();
}
?>
