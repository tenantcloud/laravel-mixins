<?php

namespace TenantCloud\Mixins;

use ScoutElastic\Builders\FilterBuilder;

/**
 * @mixin FilterBuilder
 *
 * Needs to be registered manually
 */
class FilterBuilderMixin
{
	/**
	 * Return match by it's ID.
	 */
	public function find(): callable
	{
		return fn ($id) => $this->where('id', '=', $id)->get()->first();
	}
}
