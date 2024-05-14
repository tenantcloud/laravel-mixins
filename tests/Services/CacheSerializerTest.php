<?php

namespace Tests\Services;

use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use TenantCloud\Mixins\Services\CacheSerializer;
use Tests\TestCase;

/**
 * @see CacheSerializer
 */
class CacheSerializerTest extends TestCase
{
	public static function serializesDataProvider(): iterable
	{
		yield [
			'64f225676093805c64e7fe5b289434d2a6159094af89e83474328eaf1cf67fa8',
			value(function () {
				$data = new stdClass();
				$data->name = 'test';

				return $data;
			}),
		];

		yield [
			'ea1c3ec9193a908b1f0921c772cff4428b90827f223f9106c3bb6368ba5c352b',
			value(function () {
				$data = new stdClass();
				$data->name = 'test2';

				return $data;
			}),
		];
	}

	#[DataProvider('serializesDataProvider')]
	public function testSerializesData(string $expectedKey, object $data): void
	{
		self::assertSame($expectedKey, CacheSerializer::serialize($data)['key']);
	}
}
