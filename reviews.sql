-- ============================================================
-- reviews.sql ― 第12回：商品レビュー（XSS の練習用）
-- 何度インポートしても同じ状態に戻る（最初に reviews を消してから作る）。
-- 投稿者は users の id で持つ。退会（users の削除）と一緒にレビューも消える。
-- ============================================================
USE ec_shop;

DROP TABLE IF EXISTS reviews;

CREATE TABLE reviews (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    item_id     INT NOT NULL,
    user_id     INT NOT NULL,
    rating      TINYINT NOT NULL,
    body        TEXT NOT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 最初から入っているレビュー（taro が「陶器のマグカップ」に書いたもの）
-- users の id は人によって違うので、メールアドレスから探して入れる
INSERT INTO reviews (item_id, user_id, rating, body)
SELECT i.id, u.id, 5, '手にしっくりなじみます。毎朝これでコーヒーを飲んでいます。'
FROM items i, users u
WHERE i.name = '陶器のマグカップ' AND u.email = 'taro@example.com';
