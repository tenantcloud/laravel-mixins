<?php

namespace TenantCloud\Mixins\Jobs;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Laravie\SerializesQuery\Eloquent;

class SerializableBuilder
{
	private string $key;

	private Builder $builder;

	public function __construct(Builder $builder)
	{
		$this->key = uniqid('chunk_job:', false);
		$this->builder = $builder;

		Cache::put($this->key, Eloquent::serialize($builder), Carbon::now()->addWeek());
	}

	public function __serialize(): array
	{
		return ['key' => $this->key];
	}

	public function __unserialize(array $data): void
	{
		$this->key = $data['key'];

		$this->builder = Eloquent::unserialize(Cache::get($this->key));
	}

	public function getBuilder(): Builder
	{
		return $this->builder;
	}
}
