<?php

namespace Tests\ArrMixin;

use Illuminate\Support\Arr;
use TenantCloud\Mixins\Mixins\ArrMixin;
use Tests\TestCase;

/**
 * @see ArrMixin::dotWithDepth()
 */
class ArrDotWithDepthTest extends TestCase
{
	private const TESTING_VALUE = [
		'prop'   => 1,
		'other'  => 2,
		'null'   => null,
		'nested' => [
			'prop'   => 3,
			'other'  => 4,
			'nested' => [
				'prop' => 5,
				'list' => [6, 7, 8],
			],
		],
		'list'     => [9, 10, 11],
		'wildcard' => [
			[
				'prop'  => 12,
				'other' => 13,
			],
			[
				'prop'   => 14,
				'other'  => 15,
				'nested' => [
					[
						'prop'  => 16,
						'other' => 17,
					],
					[
						'prop'  => 18,
						'other' => 19,
					],
				],
			],
			[
				'prop'       => 20,
				'other'      => 21,
				'additional' => 22,
			],
		],
	];

	public function testItBuildsDotNotatedArrayWithDepthLimitation(): void
	{
		$expectation = [
			'prop'          => 1,
			'other'         => 2,
			'null'          => null,
			'nested.prop'   => 3,
			'nested.other'  => 4,
			'nested.nested' => [
				'prop' => 5,
				'list' => [
					0 => 6,
					1 => 7,
					2 => 8,
				],
			],
			'list.0'     => 9,
			'list.1'     => 10,
			'list.2'     => 11,
			'wildcard.0' => [
				'prop'  => 12,
				'other' => 13,
			],
			'wildcard.1' => [
				'prop'   => 14,
				'other'  => 15,
				'nested' => [
					0 => [
						'prop'  => 16,
						'other' => 17,
					],
					1 => [
						'prop'  => 18,
						'other' => 19,
					],
				],
			],
			'wildcard.2' => [
				'prop'       => 20,
				'other'      => 21,
				'additional' => 22,
			],
		];

		$this->assertSame($expectation, Arr::dotWithDepth(self::TESTING_VALUE, '', 2));
	}

	public function testItBuildsDotNotatedArrayWithDepthLimitationAndPrefix(): void
	{
		$expectation = [
			'prefix.prop'               => 1,
			'prefix.other'              => 2,
			'prefix.null'               => null,
			'prefix.nested.prop'        => 3,
			'prefix.nested.other'       => 4,
			'prefix.nested.nested.prop' => 5,
			'prefix.nested.nested.list' => [
				0 => 6,
				1 => 7,
				2 => 8,
			],
			'prefix.list.0'            => 9,
			'prefix.list.1'            => 10,
			'prefix.list.2'            => 11,
			'prefix.wildcard.0.prop'   => 12,
			'prefix.wildcard.0.other'  => 13,
			'prefix.wildcard.1.prop'   => 14,
			'prefix.wildcard.1.other'  => 15,
			'prefix.wildcard.1.nested' => [
				0 => [
					'prop'  => 16,
					'other' => 17,
				],
				1 => [
					'prop'  => 18,
					'other' => 19,
				],
			],
			'prefix.wildcard.2.prop'       => 20,
			'prefix.wildcard.2.other'      => 21,
			'prefix.wildcard.2.additional' => 22,
		];

		$this->assertSame($expectation, Arr::dotWithDepth(self::TESTING_VALUE, 'prefix.', 3));
	}
}
