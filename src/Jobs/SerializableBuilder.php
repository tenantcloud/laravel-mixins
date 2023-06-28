<?php

namespace TenantCloud\Mixins\Jobs;

use AnourValar\EloquentSerialize\Facades\EloquentSerializeFacade;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TenantCloud\Mixins\Services\CacheSerializer;

/**
 * @template TModel of Model
 */
class SerializableBuilder
{
	/**
	 * @param Builder<TModel> $builder
	 */
	public function __construct(
		public readonly Builder $builder
	) {}

	public function __serialize(): array
	{
		return CacheSerializer::serialize($this->builder, [EloquentSerializeFacade::class, 'serialize']);
	}

	/**
	 * @param array{ key: string } $data
	 */
	public function __unserialize(array $data): void
	{
		$this->builder = CacheSerializer::unserialize($data, [EloquentSerializeFacade::class, 'unserialize']);
	}
}
