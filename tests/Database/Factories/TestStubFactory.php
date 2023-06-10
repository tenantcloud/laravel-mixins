<?php

namespace Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Database\Models\TestStub;

class TestStubFactory extends Factory
{
	/** @inheritDoc */
	protected $model = TestStub::class;

	/**
	 * @inheritDoc
	 */
	public function definition()
	{
		return [
			'name' => $this->faker->name,
		];
	}

	public function hasChildren(self $factory): self
	{
		return $this->has($factory, 'children');
	}
}
