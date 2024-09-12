<?php

namespace TenantCloud\Mixins\Settings\ChunkWithQueue;

use Illuminate\Support\Str;

class QueryOptions
{
	/** @var string keyName can contain table name as part of combined key, but should have only simple key for model attribute access */
	public readonly string $attributeKeyName;

	/**
	 * @param string $keyName unique key for chunk DB query
	 */
	public function __construct(
		public readonly string $keyName,
		?string $attributeKeyName = null,
		public readonly bool $getMaxKeyValueFromNewModelQuery = false,
		public readonly bool $getMinKeyValueFromNewModelQuery = false,
	) {
		$this->attributeKeyName = $attributeKeyName ?? Str::contains($this->keyName, '.')
				? trim(explode('.', $this->keyName)[1], '\'"')
				: $this->keyName;
	}

	public static function defaultInstance(): self
	{
		return new self('id');
	}

	public static function defaultInstanceFromWholeTableQuery(): self
	{
		return new self('id', null, true, true);
	}
}
