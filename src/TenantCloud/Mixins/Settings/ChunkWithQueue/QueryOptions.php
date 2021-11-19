<?php

namespace TenantCloud\Mixins\Settings\ChunkWithQueue;

class QueryOptions
{
	/** Unique key for chunk DB query. */
	public string $keyName;

	public function __construct(string $keyName)
	{
		$this->keyName = $keyName;
	}

	public static function defaultInstance(): self
	{
		return new self('id');
	}
}
