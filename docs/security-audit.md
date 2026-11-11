# 出力エスケープの監査レポート

第12回の課題（上級）。自分の `03_商品カタログ` で、**DB やフォームから来た値を画面に出している箇所**をすべて書き出し、`htmlspecialchars()` を通っているか確かめる。
このファイルは `docs/security-audit.md` として自分のリポジトリに置き、PRで提出する。

## ① 調べ方

- VS Code で `Ctrl` + `Shift` + `F` → `<?=` を検索し、出てきた行を上から順に見る
- `echo` で HTML を組み立てている行も検索する

## ② 出力している箇所の一覧

（解答例。第12回終了時点の solution を調べたもの。固定の文字だけを出している行は省いた）

| ファイル | 行 | 出している値 | どこから来た値か | エスケープ |
|---|---|---|---|---|
| public_html/reviews.php | 65 | `$r['body']` | レビュー投稿（他人が入力） | ○（直す前は ✕） |
| public_html/reviews.php | 62 | `$r['name']` | 会員登録の氏名（他人が入力） | ○ |
| public_html/reviews.php | 38, 53 | `$item['name']` | 商品登録（管理者が入力） | ○ |
| public_html/reviews.php | 63 | 評価の ★ | `(int)` で数値にした rating | ―（数値） |
| public_html/item.php | 23, 32 | `$item['name']` | 商品登録（管理者が入力） | ○ |
| public_html/item.php | 34 | `$item['description']` | 商品登録（管理者が入力） | ○（`nl2br` の前に通す） |
| public_html/item.php | 30 | `$item['image']` | アップロードしたファイル名 | ○ |
| public_html/item.php | 33, 35 | 価格・在庫 | `number_format()`・`(int)` | ―（数値） |
| public_html/items.php | 100 | `$keyword` | 検索キーワード（URL の `?keyword=`） | ○ |
| public_html/items.php | 104, 105 | `$c` | カテゴリ（DB） | ○ |
| public_html/items.php | 123, 124 | `$item['image']`・`$item['name']` | 商品登録（管理者が入力） | ○ |
| public_html/items.php | 132〜142 | `buildQuery()` | URL の検索条件 | ○（`http_build_query()` が `<` `"` などを `%3C` `%22` に変える） |
| public_html/mypage.php | 32 | `$user['name']` | 会員登録の氏名（本人が入力） | ○ |
| public_html/login.php | 20 | `$_SESSION['login_error']` | プログラムが入れたメッセージ | ○ |
| public_html/register.php | 38, 41 | `$old['name']`・`$old['email']` | 会員登録フォームの再表示 | ○ |
| public_html/register.php | 39〜46 | `field_error()` | プログラムが入れたメッセージ | ○（関数の中で通している） |
| public_html/admin/item-add.php | 62〜72 | 入力値の再表示・エラー | 商品登録フォーム | ○ |
| public_html/admin/item-edit.php | 76〜89 | 商品の各項目 | 商品登録（管理者が入力） | ○ |
| public_html/admin/item-list.php | 33, 36 | 商品名・カテゴリ | 商品登録（管理者が入力） | ○ |
| public_html/withdraw.php | 30 | `$_SESSION['csrf_token']` | `bin2hex()` で作ったトークン | ○ |

## ③ 直したところ

- `public_html/reviews.php` 65行目：`<?= $r['body'] ?>` → `<?= htmlspecialchars($r['body']) ?>`（配布時の XSS。初級で直したもの）
- それ以外に ✕ は無かった

## ④ 気づいたこと

- 「管理者しか入力しない値」でも通しておく。管理者のアカウントが乗っ取られたら、そこから全員の画面にスクリプトを仕込める
- 忘れやすいのは、レビューのように「1回書いたら他人の画面に何度も出る」値。投稿した本人ではなく、見た人全員が被害を受ける
- `<input value="…">` の中に出すときも通す。`"` を閉じられると、属性を足されてしまう
