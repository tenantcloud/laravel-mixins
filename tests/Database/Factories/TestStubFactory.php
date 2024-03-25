<?php

namespace Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Database\Models\TestStub;

/**
 * @template-extends Factory<TestStub>
 */
class TestStubFactory extends Factory
{
	protected $model = TestStub::class;

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
