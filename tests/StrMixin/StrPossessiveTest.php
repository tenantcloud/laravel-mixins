<?php

namespace Tests\StrMixin;

use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use TenantCloud\Mixins\Mixins\StrMixin;
use Tests\TestCase;

/** @see StrMixin::possessive() */
class StrPossessiveTest extends TestCase
{
	#[DataProvider('replacesLastRegexProvider')]
	public function testReplacesLastRegex(string $value, string $expectedResult): void
	{
		self::assertSame(
			$expectedResult,
			Str::possessive($value),
		);
	}

	public static function replacesLastRegexProvider(): iterable
	{
		yield [
			'Greg',
			'Greg\'s',
		];

		yield [
			'Charles',
			'Charles\'',
		];
	}
}
