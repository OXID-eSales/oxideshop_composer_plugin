<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Utilities;

class PackageUpdatePreferenceChecker
{
    public const UPDATE_EXTRA_KEY_YES = 'update-answer-yes';
    public const UPDATE_EXTRA_KEY_NO = 'update-answer-no';

    public const PREFERENCE_MISSCONFIGURED_ERROR = 'Missconfigured update preference value, check documentation.';

    public function __construct(
        private readonly array $extras,
    ) {
    }

    public function getUpdatePreferenceValue(string $packageName): ?bool
    {
        if (
            isset($this->extras[self::UPDATE_EXTRA_KEY_YES])
            && $this->checkPreferenceConfigurationIsArray(self::UPDATE_EXTRA_KEY_YES)
            && in_array($packageName, $this->extras[self::UPDATE_EXTRA_KEY_YES])
        ) {
            return true;
        }

        if (
            isset($this->extras[self::UPDATE_EXTRA_KEY_NO])
            && $this->checkPreferenceConfigurationIsArray(self::UPDATE_EXTRA_KEY_NO)
            && in_array($packageName, $this->extras[self::UPDATE_EXTRA_KEY_NO])
        ) {
            return false;
        }

        return null;
    }

    /**
     * @throws \Exception
     */
    private function checkPreferenceConfigurationIsArray(string $key): bool
    {
        if (!is_array($this->extras[$key])) {
            throw new \Exception(self::PREFERENCE_MISSCONFIGURED_ERROR);
        }

        return true;
    }
}
