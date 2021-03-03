# laravel-generalize-validationrules

Laravelのバリデーションルールを共通化するやつ


## インストール方法

というにはおこがましいですが、ファイル1つだけなんで `ValidationRules.php` を app 直下にコピーしてださい

※違うディレクトリに置く場合は、名前空間をよしなに書き換えてください

## 使い方

### ルールを定義する

`RULES` にバリデーションルールを定義します

```php
protected const RULES = [
    'posts' => [
        'title' => ['string', 'between:10,255'],
        'body' => ['string'],
        'publish_at' => ['date'],
    ],
];
```

 - ネストすることもできます
 - ルールは配列記法、 `|` 区切りの文字列記法に対応しています

### ルールを取得する

FormRequest などバリデーションルールを取得したいところで `getRules()` を呼び出します

```php
public function rules(\App\ValidationRules $rules)
{
    return $rules->getRules([
        'posts.title' => ['required'],
        'posts.body',
        'posts.publish_at',
    ]);
}
```

 - ネストされたルールは `.` (ドット)記法で書いてください
 - 追加したいルールがあれば、連想配列にして渡してあげてください
 - 定義していないルールを取得しようとすると `OutOfRangeException` がスローされます

上の例では以下のルールが返ります

```php
[
  'title' => ['required', 'string', 'between:10,255'],
  'body' => ['string'],
  'publish_at' => ['date'],
]
```

### パラメーター名に別名をつける

`users.name` と `pets.name` のルールを取得しようとすると、後に指定した `pets.name` のルールのみ `name` のキーで返ってきます

このような状況を避けるには第2引数で別名を指定します

```php
$keyAliases = [
    'users.name' => 'user_name',
    'pets.name' => 'pet_name',
];

app(\App\ValidationRules::class)->getRules(
    [
        'users.name',
        'pets.name',
    ],
    $keyAliases
);
```

また、常に別名を付けたいパラメータ名がある場合は `ValidationRules::KEY_ALIASES` に定義してください

`getRules()` の第2引数
`KEY_ALIASES` 定数
ドット区切りの最後の文字列(デフォルト)
の優先順位でパラメータ名が採用されます

※パラメータ名を変えた場合は、`required_if` のような他のパラメータを参照するルールに気をつけてください
※また、 `attributes` にも気をつけてください 

## カスタマイズする

### ルールを定義する場所を変更する

`ValidationRules::RULES` じゃない場所にしたい場合は `all()` を書き換えるか、 `ValidationRules` を継承してオーバーライドしてください

### 指定したルールが見つからなかった場合の挙動を変えたい

デフォルトでは定義していないルールを取得しようとすると `OutOfRangeException` がスローされます

定義していないルールを取得しようとしたとき、空配列を返したい場合は `notFound()` を書き換えるか、 `ValidationRules` を継承してオーバーライドしてください

