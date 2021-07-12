<?php

namespace Tests\StrMixin;

use Illuminate\Support\Str;
use TenantCloud\Mixins\StrMixin;
use Tests\TestCase;

/**
 * @see StrMixin::extractApostrophes
 */
class StrExtractApostrophesTest extends TestCase
{
	public function testItApostrophesExtractedFromString(): void
	{
		$this->assertExtracted('OReilly', 'O\'Reilly');
		$this->assertExtracted('McLoren', 'Mc\'Loren');
		$this->assertExtracted('Skis Mobile Welding', 'Ski’s Mobile Welding');
		$this->assertExtracted('McLauren', filter_var('Mc\'Lauren', FILTER_SANITIZE_STRING));
		$this->assertExtracted('', '\'');
	}

	protected function assertExtracted(string $expected, string $value): void
	{
		$this->assertEquals($expected, Str::extractApostrophes($value));
	}
}
