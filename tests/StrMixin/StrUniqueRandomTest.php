<?php

namespace Tests\StrMixin;

use Illuminate\Support\Str;
use TenantCloud\Mixins\Mixins\StrMixin;
use Tests\TestCase;

/** @see StrMixin::uniqueRandom */
class StrUniqueRandomTest extends TestCase
{
	public function testStringIsOfGivenLength(): void
	{
		$this->assertEquals(32, mb_strlen(Str::uniqueRandom()));
		$this->assertEquals(0, mb_strlen(Str::uniqueRandom(0)));
		$this->assertEquals(16, mb_strlen(Str::uniqueRandom(16)));
		$this->assertEquals(48, mb_strlen(Str::uniqueRandom(48)));
		$this->assertEquals(60, mb_strlen(Str::uniqueRandom(60)));
	}

	public function testStringsDontRepeat(): void
	{
		$strings = [];

		for ($i = 0; $i < 50; $i++) {
			$strings[] = Str::uniqueRandom();
		}

		$this->assertCount(50, $strings);
		$this->assertCount(50, array_unique($strings));
	}
}
