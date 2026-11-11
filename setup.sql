-- 注意：実習用ec_shopの全データを削除する。他のDBには実行しない。
DROP DATABASE IF EXISTS ec_shop;
-- 第14回までの再スタート用：ec_shopを削除して完全に作り直す
-- 対象：ec_shop の下記7テーブル。登録済みの商品・会員・レビュー等はすべて消える。
-- 古いDBを削除するため、列が不足していても今回の定義へ揃う。
-- DB・テーブルを再作成し、初期データを投入する。
-- アプリからの操作を止めてから、phpMyAdminでこのファイルをインポートする。

-- ============================================================
-- schema.sql
-- 第2回 DB設計とER図：解答例（この回時点の最新版）
--
-- 以降のコマ（第8回・第19回・第22回など）で、この上にさらに
-- ALTER TABLE が加わっていきます。各回の solution/schema.sql は
-- その回終了時点の完全な状態を反映しています。
-- ============================================================

CREATE DATABASE IF NOT EXISTS ec_shop DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ec_shop;

-- ------------------------------------------------------------
-- users：会員情報
-- role : 'member'（一般会員）/ 'admin'（管理者）※第9回の権限分岐で使用
-- password : 第11回で password_hash() によるハッシュ保存へ変更済み
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    email         VARCHAR(255) UNIQUE NOT NULL,
    password      VARCHAR(255) NOT NULL,
    name          VARCHAR(100) NOT NULL,
    address       VARCHAR(255),
    role          ENUM('member', 'admin') NOT NULL DEFAULT 'member',
    failed_count  INT NOT NULL DEFAULT 0,
    locked_until  DATETIME NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- items：商品情報
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS items (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(255) NOT NULL,
    price         INT NOT NULL,
    description   TEXT,
    image         VARCHAR(255),
    stock         INT NOT NULL DEFAULT 0,
    category      VARCHAR(100),
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- cart：誰が・どの商品を・いくつカートに入れているか
-- 第14回：user_id + item_id の組み合わせで重複を防ぐ設計にする
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cart (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    item_id       INT NOT NULL,
    quantity      INT NOT NULL DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (item_id) REFERENCES items(id)
);

-- ------------------------------------------------------------
-- favorites：多対多の中間テーブル（第16回で複合主キーの意味を扱う）
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS favorites (
    user_id       INT NOT NULL,
    item_id       INT NOT NULL,
    PRIMARY KEY (user_id, item_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (item_id) REFERENCES items(id)
);

-- ------------------------------------------------------------
-- orders：注文の本体（1回の注文につき1行）
-- status : 'pending'（未決済）/ 'paid'（決済済み）/ 'cancelled'（キャンセル） ※第19回で使用
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    order_date    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total_price   INT NOT NULL,
    status        ENUM('pending', 'paid', 'cancelled') NOT NULL DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- ------------------------------------------------------------
-- order_details：注文の明細（1回の注文に複数商品が含まれる）
-- price は注文当時の単価をコピーして保持する（items.priceが後で変わっても、
-- 過去の注文金額が変わらないようにするため）
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_details (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    order_id      INT NOT NULL,
    item_id       INT NOT NULL,
    quantity      INT NOT NULL,
    price         INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (item_id) REFERENCES items(id)
);


-- ============================================================
-- 第12回：商品レビュー（XSS の練習用。内容は reviews.sql と同じ）
-- ============================================================
CREATE TABLE IF NOT EXISTS reviews (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    item_id     INT NOT NULL,
    user_id     INT NOT NULL,
    rating      TINYINT NOT NULL,
    body        TEXT NOT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 外部キーで参照する側（子）から先に消す。外部キーのチェックは無効にしない。
START TRANSACTION;
DELETE FROM order_details;
DELETE FROM orders;
DELETE FROM cart;
DELETE FROM favorites;
DELETE FROM reviews;
DELETE FROM items;
DELETE FROM users;

-- IDも固定して入れ直すため、商品1・会員1・レビュー1から始まる。
INSERT INTO items (id, name, price, description, image, stock, category) VALUES
(1, '陶器のマグカップ', 2400, '職人が手作りする、あたたかみのある陶器のマグカップです。', NULL, 12, 'マグカップ'),
(2, '青釉のマグカップ', 2600, '深い青色が特徴の釉薬を使ったマグカップです。', NULL, 8, 'マグカップ'),
(3, '木製コースター 4枚セット', 1200, '天然木を使ったコースターの4枚セットです。', NULL, 20, 'コースター'),
(4, '陶器の一輪挿し', 1800, '小さな一輪挿し。食卓に彩りを添えます。', NULL, 15, '花器'),
(5, '陶器の小皿', 900, '副菜にちょうど良いサイズの小皿です。', NULL, 30, '食器'),
(6, '木製カッティングボード', 3200, '一枚板から作られたカッティングボードです。', NULL, 6, 'キッチン雑貨'),
(7, '陶器のティーポット', 4500, '和洋どちらにも合うシンプルなティーポットです。', NULL, 5, '食器'),
(8, '限定生産マグカップ', 3600, '数量限定で制作されたマグカップです。', NULL, 1, 'マグカップ'),
(9, '木製トレー', 2100, '朝食やカフェタイムにちょうど良いトレーです。', NULL, 10, 'キッチン雑貨'),
(10, '陶器の茶碗', 1500, '毎日の食卓になじむ茶碗です。', NULL, 18, '食器'),
(11, '木製スプーン', 800, '手に馴染む木製のカトラリーです。', NULL, 25, 'キッチン雑貨'),
(12, '陶器の花瓶', 2900, '存在感のある大ぶりな花瓶です。', NULL, 7, '花器');

INSERT INTO users (id, email, password, name, address, role) VALUES
(1, 'taro@example.com',  '$2y$10$9j7D9HPqYo3urHIjbFxJdeWXdwZEU5TVQW0l32Mep1PmeLlKySiw2', '山田 太郎', '東京都新宿区1-1-1', 'member'),
(2, 'admin@example.com', '$2y$10$Gnyre0JIRtcyijnhtwj5yOqxNIR0u/4v29k3UtBFXF2VVM/0jmRBK', '管理者',   '東京都新宿区1-1-1', 'admin');

INSERT INTO reviews (id, item_id, user_id, rating, body)
SELECT 1, i.id, u.id, 5, '手にしっくりなじみます。毎朝これでコーヒーを飲んでいます。'
FROM items i, users u
WHERE i.name = '陶器のマグカップ' AND u.email = 'taro@example.com';

COMMIT;

-- DELETEだけでは戻らない連番を調整する（データ投入の確定後）。
-- データがあるテーブルは最大IDの次、空のテーブルは1から採番される。
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE items AUTO_INCREMENT = 1;
ALTER TABLE reviews AUTO_INCREMENT = 1;
ALTER TABLE cart AUTO_INCREMENT = 1;
ALTER TABLE orders AUTO_INCREMENT = 1;
ALTER TABLE order_details AUTO_INCREMENT = 1;
