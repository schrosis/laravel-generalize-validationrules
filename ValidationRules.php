<?php

namespace App;

use Illuminate\Support\Arr;

class ValidationRules
{
    /**
     * バリデーションルール
     * required、nullable、sometimesなどはここでは定義しない
     */
    private const RULES = [];

    /**
     * デフォルトのパラメータ名の別名
     */
    private const KEY_ALIASES = [];

    /**
     * バリデーションルールを取得します
     *
     * @param array $keys
     * @param array $keyAliases
     * @return array
     */
    public function getRules(array $keys, array $keyAliases = []): array
    {
        $rules = [];
        foreach ($keys as $key => $additionalRules) {
            if (is_int($key) && is_string($additionalRules)) {
                [$key, $additionalRules] = [$additionalRules, []];
            }

            $rules[$this->keyName($key, $keyAliases)] = $this->getRule($key, $additionalRules);
        }

        return $rules;
    }

    /**
     * 単一のパラメータのバリデーションルールを取得します
     * 第2引数でバリデーションルールを先頭に追加できます
     * requiredやnullable、sometimesなどは第2引数で追加してください
     *
     * @param string $key
     * @param string|array $additionalRules
     * @return array
     */
    public function getRule(string $key, $additionalRules = []): array
    {
        if (is_string($additionalRules)) {
            $additionalRules = explode('|', $additionalRules);
        }

        $rules = Arr::get($this->all(), $key, $this->notFound($key));
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        return array_filter(
            array_unique(array_merge($additionalRules, $rules)),
            function ($rule) {
                return $rule !== '';
            }
        );
    }

    protected function all(): array
    {
        return self::RULES;
    }

    /**
     * バリデーションデータのパラメータ名を取得します
     * エイリアスが存在しない場合はドット区切りの最後の文字列になります
     *
     * @param string $key
     * @param array $keyAliases
     * @return string
     */
    protected function keyName(string $key, array $keyAliases = []): string
    {
        return $keyAliases[$key]
            ?? self::KEY_ALIASES[$key]
            ?? Arr::last(explode('.', $key));
    }

    /**
     * 指定されたバリデーションルールのキーが存在しなかった場合に呼ばれます
     *
     * @param string $key
     * @return string|array|\Closure
     */
    protected function notFound(string $key)
    {
        return function () use ($key) {
            throw new \OutOfRangeException(
                sprintf('[%s]のバリデーションルールが見つかりませんでした', $key)
            );
        };
    }
}
