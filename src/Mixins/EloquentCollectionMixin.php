<?php

namespace TenantCloud\Mixins\Mixins;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Tests\EloquentCollectionMixin\EloquentCollectionReplicateTest;

/**
 * @mixin Collection
 */
class EloquentCollectionMixin
{
	/**
	 * Replicates every model in the collection.
	 *
	 * @see EloquentCollectionReplicateTest
	 */
	public function replicate(): callable
	{
		return fn (?array $except = null): Collection => $this->map(fn (Model $model) => $model->replicate($except));
	}
}
