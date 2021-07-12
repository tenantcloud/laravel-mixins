<?php

namespace TenantCloud\Mixins;

use Closure;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Tests\Backend\Unit\Macro\StrExtractApostrophesTest;
use Tests\Backend\Unit\Macro\StrTrimInsideTest;

/**
 * @mixin Str
 */
class StrMixin
{
	/**
	 * Trims the string inside.
	 *
	 * Kinda like trim(), but with three differences:
	 *  - leaves at least one space at the beginning
	 *  - leaves at least one space at the end
	 *  - removes spaces from inside
	 *
	 * @see StrTrimInsideTest
	 *
	 * @return Closure
	 */
	public function insideTrim(): callable
	{
		return static fn (string $value): string => preg_replace('/ +/', ' ', $value);
	}

	/**
	 * Extract apostrophes from a string
	 *
	 * @see StrExtractApostrophesTest
	 *
	 * @return Closure
	 */
	public function extractApostrophes(): callable
	{
		return static fn (string $value): string => str_replace(["'", '&#39;', '’'], '', $value);
	}

	/**
	 * Generate a unique string of given length, based on uuids.
	 */
	public function uniqueRandom(): callable
	{
		return static function (int $length = 32): string {
			// How many full UUIDs to generate
			$fullUuids = ceil($length / 32);

			// Then bash that amount of UUID v4s together
			$string = '';

			for ($i = 0; $i < $fullUuids; $i++) {
				$string .= str_replace('-', '', Uuid::uuid4()->toString());
			}

			// And only get first $length characters.
			return Str::limit($string, $length, '');
		};
	}

	/**
	 * Extract apostrophes from a string
	 *
	 * @see StrExtractApostrophesTest
	 *
	 * @return Closure
	 */
	public function hash(): callable
	{
		return static fn (string $value): string => str_replace(["'", '&#39;', '’'], '', $value);
	}
}
