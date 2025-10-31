<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Tests\Unit\Utilities;

use OxidEsales\ComposerPlugin\Utilities\PackageUpdatePreferenceChecker;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PackageUpdatePreferenceCheckerTest extends TestCase
{
    #[Test]
    #[DataProvider('updatePreferenceCases')]
    public function checkAllCases(array $extras, string $packageName, ?bool $expectedValue): void
    {
        $sut = new PackageUpdatePreferenceChecker($extras);
        $result = $sut->getUpdatePreferenceValue($packageName);

        $this->assertSame($expectedValue, $result);
    }

    public static function updatePreferenceCases(): \Generator
    {
        $packageName = uniqid();

        yield 'empty extras' => [
            'extras' => [],
            'packageName' => $packageName,
            'expectedValue' => null,
        ];

        yield 'extras without preferences' => [
            'extras' => [
                uniqid() => uniqid(),
            ],
            'packageName' => $packageName,
            'expectedValue' => null,
        ];

        yield 'extras with preference as no' => [
            'extras' => [
                PackageUpdatePreferenceChecker::UPDATE_EXTRA_KEY_NO => [
                    uniqid(),
                    $packageName,
                    uniqid(),
                ]
            ],
            'packageName' => $packageName,
            'expectedValue' => false,
        ];

        yield 'extras with preference as yes' => [
            'extras' => [
                PackageUpdatePreferenceChecker::UPDATE_EXTRA_KEY_YES => [
                    uniqid(),
                    $packageName,
                    uniqid(),
                ]
            ],
            'packageName' => $packageName,
            'expectedValue' => true,
        ];

        yield 'extras with both preferences gives true' => [
            'extras' => [
                PackageUpdatePreferenceChecker::UPDATE_EXTRA_KEY_NO => [
                    uniqid(),
                    $packageName,
                    uniqid(),
                ],
                PackageUpdatePreferenceChecker::UPDATE_EXTRA_KEY_YES => [
                    uniqid(),
                    $packageName,
                    uniqid(),
                ]
            ],
            'packageName' => $packageName,
            'expectedValue' => true,
        ];
    }

    #[Test]
    #[DataProvider('updatePreferenceMissconfiguredCases')]
    public function checkMissconfiguredCases(array $extras): void
    {
        $sut = new PackageUpdatePreferenceChecker($extras);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($sut::PREFERENCE_MISSCONFIGURED_ERROR);

        $sut->getUpdatePreferenceValue(uniqid());
    }

    public static function updatePreferenceMissconfiguredCases(): \Generator
    {
        yield 'missconfigured preference no value as not array' => [
            'extras' => [
                PackageUpdatePreferenceChecker::UPDATE_EXTRA_KEY_NO => uniqid(),
            ]
        ];

        yield 'missconfigured preference yes value as not array' => [
            'extras' => [
                PackageUpdatePreferenceChecker::UPDATE_EXTRA_KEY_YES => uniqid(),
            ]
        ];
    }
}
