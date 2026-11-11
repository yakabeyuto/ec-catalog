<?php
/**
 * items.php ― 商品一覧・キーワード検索・カテゴリ絞り込み・並び替え・ページネーション
 *
 * 初級：一覧表示
 * 中級：LIKE検索・カテゴリ絞り込み・0件時の表示
 * 上級：検索条件を保ったままのページネーション
 *
 * 第5回 feature/item-sort：並び替え機能を追加
 * （小さな機能追加をブランチで実装しPRを作る、という第5回の演習素材）
 */
require_once __DIR__ . '/../includes/config.php';

// ---- ① 検索条件の取得 ----------------------------------------------------
$keyword  = trim($_GET['keyword'] ?? '');
$category = trim($_GET['category'] ?? '');
$page     = max(1, (int) ($_GET['page'] ?? 1));
$perPage  = 9;

// 並び替え：ORDER BYの列名・方向はプレースホルダにできないため、
// 必ずホワイトリスト（許可リスト）に照合してから使う。
// $_GET['sort'] の値をそのままORDER BYに埋め込むのは、SQLインジェクションの危険がある。
$sortOptions = [
    'new'        => 'created_at DESC',
    'price_asc'  => 'price ASC',
    'price_desc' => 'price DESC',
];
$sort    = $_GET['sort'] ?? 'new';
$orderBy = $sortOptions[$sort] ?? $sortOptions['new'];

// ---- ② WHERE句を動的に組み立てる（必ずプレースホルダを使う）----------------
$conditions = [];
$params     = [];

if ($keyword !== '') {
    // LIKE検索でワイルドカード文字（% と _）が特別な意味を持ってしまうため、
    // 検索キーワードに含まれていた場合はエスケープしてから渡す。
    $escapedKeyword = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $keyword);
    $conditions[]   = "name LIKE :keyword ESCAPE '\\\\'";
    $params['keyword'] = '%' . $escapedKeyword . '%';
}

if ($category !== '') {
    $conditions[] = 'category = :category';
    $params['category'] = $category;
}

$whereSql = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

// ---- ③ 該当件数を取得し、総ページ数を計算する ------------------------------
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM items {$whereSql}");
$countStmt->execute($params);
$totalCount = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalCount / $perPage));
$page       = min($page, $totalPages); // 存在しない末尾ページへの飛び越し対策
$offset     = ($page - 1) * $perPage;

// ---- ④ 商品一覧を取得する ---------------------------------------------------
// LIMIT/OFFSETはプレースホルダにすると型の扱いが環境依存になりやすいため、
// int化した値を直接埋め込む（ユーザー入力を直接使っていない点に注意）。
$sql = "SELECT id, name, price, image, stock, category
        FROM items
        {$whereSql}
        ORDER BY {$orderBy}
        LIMIT {$perPage} OFFSET {$offset}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

// カテゴリ一覧（絞り込みのプルダウン用）
$categories = $pdo->query('SELECT DISTINCT category FROM items WHERE category IS NOT NULL ORDER BY category')
                   ->fetchAll(PDO::FETCH_COLUMN);

// ページ送りリンクで検索条件を保持するためのクエリ文字列を組み立てる
function buildQuery(array $overrides = []): string
{
    $base = [
        'keyword'  => $_GET['keyword'] ?? '',
        'category' => $_GET['category'] ?? '',
        'sort'     => $_GET['sort'] ?? 'new',
        'page'     => $_GET['page'] ?? 1,
    ];
    return http_build_query(array_merge($base, $overrides));
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>商品一覧 ― Tsuchi-to-Hi</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header><a href="index.php">Tsuchi-to-Hi</a></header>
    <main>
        <h1>商品一覧</h1>

        <form class="search-form" method="get" action="items.php">
            <input type="text" name="keyword" placeholder="商品名で検索"
                   value="<?= htmlspecialchars($keyword) ?>">
            <select name="category">
                <option value="">すべてのカテゴリ</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>" <?= $c === $category ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="sort">
                <option value="new" <?= $sort === 'new' ? 'selected' : '' ?>>新着順</option>
                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>価格が安い順</option>
                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>価格が高い順</option>
            </select>
            <button type="submit">検索</button>
        </form>

        <?php if (empty($items)): ?>
            <p class="empty-state">該当する商品が見つかりませんでした。</p>
        <?php else: ?>
            <div class="item-grid">
                <?php foreach ($items as $item): ?>
                    <a class="item-card" href="item.php?id=<?= (int) $item['id'] ?>" style="text-decoration:none; color:inherit;">
                        <img src="<?= $item['image'] ? 'uploads/items/' . htmlspecialchars($item['image']) : 'https://placehold.co/220x140' ?>" alt="">
                        <div class="name"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="price">¥<?= number_format($item['price']) ?></div>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="items.php?<?= buildQuery(['page' => $page - 1]) ?>">‹ 前へ</a>
                <?php endif; ?>
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <?php if ($p === $page): ?>
                        <span class="current"><?= $p ?></span>
                    <?php else: ?>
                        <a href="items.php?<?= buildQuery(['page' => $p]) ?>"><?= $p ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="items.php?<?= buildQuery(['page' => $page + 1]) ?>">次へ ›</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
