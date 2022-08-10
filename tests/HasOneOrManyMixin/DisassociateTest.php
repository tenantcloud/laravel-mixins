<?php

namespace Tests\HasOneOrManyMixin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use TenantCloud\Mixins\Mixins\HasOneOrManyMixin;
use Tests\Database\Factories\TestStubFactory;
use Tests\TestCase;

/** @see HasOneOrManyMixin::disassociate() */
class DisassociateTest extends TestCase
{
	use RefreshDatabase;

	public function testDisassociatesHasMany(): void
	{
		$stub = TestStubFactory::new()
			->hasChildren(TestStubFactory::new())
			->create();

		$child = $stub->children[0];

		$stub->children()->disassociate();

		self::assertSame($stub->id, $child->parent_id);

		$child->refresh();

		self::assertNull($child->parent_id);
	}
}
