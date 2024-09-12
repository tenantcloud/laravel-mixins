<?php

namespace Tests\StrMixin;

use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use TenantCloud\Mixins\Mixins\StrMixin;
use Tests\TestCase;

/** @see StrMixin::replaceLastRegex() */
class StrReplaceLastRegexTest extends TestCase
{
	#[DataProvider('replacesLastRegexProvider')]
	public function testReplacesLastRegex(string $expected, string $input, int $skip): void
	{
		self::assertSame(
			$expected,
			Str::replaceLastRegex($input, '/[0-9]/', '*', $skip),
		);
	}

	public static function replacesLastRegexProvider(): iterable
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
