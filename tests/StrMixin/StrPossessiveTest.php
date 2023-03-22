<?php

namespace Tests\StrMixin;

use Generator;
use Illuminate\Support\Str;
use TenantCloud\Mixins\Mixins\StrMixin;
use Tests\TestCase;

/** @see StrMixin::possessive() */
class StrPossessiveTest extends TestCase
{
	/**
	 * @dataProvider possessiveProvider
	 */
	public function testReplacesLastRegex(string $value, string $expectedResult): void
	{
		self::assertSame(
			$expectedResult,
			Str::possessive($value),
		);
	}

	public function possessiveProvider(): Generator
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
