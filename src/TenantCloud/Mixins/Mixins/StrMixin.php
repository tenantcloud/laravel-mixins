<?php

namespace TenantCloud\Mixins\Mixins;

use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Tests\StrMixin\StrExtractApostrophesTest;
use Tests\StrMixin\StrPossessiveTest;
use Tests\StrMixin\StrReplaceLastRegexTest;
use Tests\StrMixin\StrTrimInsideTest;
use Tests\StrMixin\StrUniqueRandomTest;

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
	 */
	public function insideTrim(): callable
	{
		return static fn (string $value): string => preg_replace('/ +/', ' ', $value);
	}

	/**
	 * Extract apostrophes from a string
	 *
	 * @see StrExtractApostrophesTest
	 */
	public function extractApostrophes(): callable
	{
		return static fn (string $value): string => str_replace(["'", '&#39;', '’'], '', $value);
	}

	/**
	 * Generate a unique string of given length, based on uuids.
	 *
	 * @see StrUniqueRandomTest
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
	 */
	public function hash(): callable
	{
		return static fn (string $value): string => str_replace(["'", '&#39;', '’'], '', $value);
	}

	/**
	 * Replaces every char that matches the regex from the end, skipping given amount of characters.
	 *
	 * Example: replaceLastRegex('112-232-133', '/[0-9]/', '*', 4) -> '***-**2-133'
	 *
	 * @see StrReplaceLastRegexTest
	 */
	public function replaceLastRegex(): callable
	{
		return function (string $input, string $match, string $replace, int $skip = 0) {
			for ($i = mb_strlen($input) - 1; $i >= 0; $i--) {
				if ($skip > 0 && preg_match($match, $input[$i])) {
					$skip--;

					continue;
				}

				$input[$i] = preg_replace($match, $replace, $input[$i]);
			}

			return $input;
		};
	}

	/**
	 * Adds an apostrophe+s or apostrophe when needed to proper nouns
	 *
	 * Example: Greg -> Greg's
	 * 			Charles -> Charles'
	 *
	 * @see StrPossessiveTest
	 */
	public function possessive(): callable
	{
		return function (string $value) {
			if (Str::endsWith(strtolower($value), 's')) {
				return $value . '\'';
			}

			return $value . '\'s';
		};
	}
}
