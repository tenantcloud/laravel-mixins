<?php

namespace TenantCloud\Mixins;

use ScoutElastic\Builders\FilterBuilder;

/**
 * @mixin FilterBuilder
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
