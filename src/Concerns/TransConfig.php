<?php

declare(strict_types=1);

namespace Ifsware\Omnisearch\Concerns;

trait TransConfig
{
    protected function transConfig(string $configKey, string $fallbackTranslationKey): string
    {
        $value = config($configKey);
        if (is_string($value) && filled($value)) {
            return $value;
        }

        return $this->trans($fallbackTranslationKey);
    }

    /** @param array<string, bool|float|int|string|null> $params */
    protected function trans(string $key, array $params = []): string
    {
        $value = __($key, $params);

        return is_string($value) ? $value : $key;
    }
}
