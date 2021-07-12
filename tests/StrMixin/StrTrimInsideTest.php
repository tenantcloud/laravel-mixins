<?php

namespace Tests\StrMixin;

use TenantCloud\Mixins\StrMixin;
use Illuminate\Support\Str;
use Tests\TestCase;

/** @see StrMixin::insideTrim */
class StrTrimInsideTest extends TestCase
{
	public function testItTrimsTheStringFromInside(): void
	{
		$this->assertTrimmed('Hello', 'Hello');
		$this->assertTrimmed('d Hello', 'd Hello');
		$this->assertTrimmed('d Hello', 'd  Hello');
		$this->assertTrimmed('1\tHello', '1\tHello');
		$this->assertTrimmed('1\rHello', '1\rHello');
		$this->assertTrimmed('d Hello 1', 'd        Hello    1');
		$this->assertTrimmed(' k Hello', ' k   Hello');
		$this->assertTrimmed(' k1 1 Hello', '     k1  1  Hello');
		$this->assertTrimmed('Hello k1 ', 'Hello  k1   ');
	}

	protected function assertTrimmed(string $expected, string $value): void
	{
		$this->assertEquals($expected, Str::insideTrim($value));
	}
}
