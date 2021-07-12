<?php

namespace Tests\ArrMixin;

use TenantCloud\Mixins\ArrMixin;
use Illuminate\Support\Arr;
use Tests\TestCase;

/**
 * @see ArrMixin::select
 */
class ArrSelectTest extends TestCase
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

	public function testItSelectsSimpleProperties()
	{
		$this->assertSelectedTestingValue([], []);

		$this->assertSelectedTestingValue([
			'prop' => 1,
		], [
			'prop',
			'non_existent',
		]);

		$this->assertSelectedTestingValue([
			'other' => 2,
			'null'  => null,
		], [
			'other',
			'null',
		]);

		$this->assertSelectedTestingValue([
			'prop' => 1,
			'list' => [9, 10, 11],
		], [
			'prop',
			'list',
		]);
	}

	public function testItSelectsNestedProperties()
	{
		$this->assertSelectedTestingValue([
			'nested' => [
				'prop' => 3,
			],
		], [
			'nested.prop',
		]);

		$this->assertSelectedTestingValue([
			'nested' => [
				'prop'  => 3,
				'other' => 4,
			],
		], [
			'nested.prop',
			'nested.other',
		]);

		$this->assertSelectedTestingValue([
			'nested' => [
				'prop'   => 3,
				'other'  => 4,
				'nested' => [
					'prop' => 5,
					'list' => [6, 7, 8],
				],
			],
		], [
			'nested',
		]);
	}

	public function testItSelectsDeepNestedProperties()
	{
		$this->assertSelectedTestingValue([], [
			'nested.nested.non_existent',
		]);

		$this->assertSelectedTestingValue([
			'nested' => [
				'nested' => [
					'prop' => 5,
					'list' => [6, 7, 8],
				],
			],
		], [
			'nested.nested.prop',
			'nested.nested.list',
		]);
	}

	public function testItSelectsUsingWildcard()
	{
		$this->assertSelectedTestingValue([
			'wildcard' => [
				[
					'prop'  => 12,
					'other' => 13,
				],
				[
					'prop'  => 14,
					'other' => 15,
				],
				[
					'prop'  => 20,
					'other' => 21,
				],
			],
		], [
			'wildcard.*.prop',
			'wildcard.*.other',
		]);

		$this->assertSelectedTestingValue([
			'wildcard' => [
				[],
				[],
				[
					'additional' => 22,
				],
			],
		], [
			'wildcard.*.additional',
		]);
	}

	public function testItSelectsUsingNestedWildcard()
	{
		$this->assertSelectedTestingValue([
			'wildcard' => [
				[],
				[
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
				[],
			],
		], [
			'wildcard.*.nested',
		]);

		$this->assertSelectedTestingValue([
			'wildcard' => [
				[],
				[
					'nested' => [
						[
							'prop' => 16,
						],
						[
							'prop' => 18,
						],
					],
				],
				[],
			],
		], [
			'wildcard.*.nested.*.prop',
		]);
	}

	protected function assertSelectedTestingValue(array $expected, array $keys): void
	{
		$this->assertEquals($expected, Arr::select(self::TESTING_VALUE, $keys));
	}
}
