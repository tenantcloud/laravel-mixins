<?php

namespace Tests\StrMixin;

use Generator;
use Illuminate\Support\Str;
use TenantCloud\Mixins\Mixins\StrMixin;
use Tests\TestCase;

/** @see StrMixin::replaceLastRegex() */
class StrReplaceLastRegexTest extends TestCase
{
	/**
	 * @dataProvider replacesLastRegexProvider
	 */
	public function testReplacesLastRegex(string $expected, string $input, int $skip): void
	{
		self::assertSame(
			$expected,
			Str::replaceLastRegex($input, '/[0-9]/', '*', $skip),
		);
	}

	public function replacesLastRegexProvider(): Generator
	{
		yield [
			'***-**-****',
			'112-23-2133',
			0,
		];

		yield [
			'***-**-2133',
			'112-23-2133',
			4,
		];

		yield [
			'***-**2-133',
			'112-232-133',
			4,
		];

		yield [
			'112-232-133',
			'112-232-133',
			100,
		];
	}
}
