<?php

namespace TenantCloud\Mixins\Jobs;

use AnourValar\EloquentSerialize\Facades\EloquentSerializeFacade;
use Illuminate\Database\Eloquent\Builder;
use TenantCloud\Mixins\Services\CacheSerializer;

class SerializableBuilder
{
	private Builder $builder;

	public function __construct(Builder $builder)
	{
		$this->builder = $builder;
	}

	public function __serialize(): array
	{
		return CacheSerializer::serialize($this->builder, [EloquentSerializeFacade::class, 'serialize']);
	}

	public function __unserialize(array $data): void
	{
		$this->builder = CacheSerializer::unserialize($data, [EloquentSerializeFacade::class, 'unserialize']);
	}

	public function getBuilder(): Builder
	{
		return $this->builder;
	}
}
