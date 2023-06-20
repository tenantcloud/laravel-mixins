<?php

namespace TenantCloud\Mixins\Settings\ChunkWithQueue;

use Illuminate\Support\Str;

/**
 * @property-read string $keyName
 * @property-read string $attributeKeyName
 */
class QueryOptions
{
	/** Unique key for chunk DB query. */
	protected string $keyName;

	/** keyName can contain table name as part of combined key, but $attributeKeyName should have only simple key for model attribute access */
	protected string $attributeKeyName;

	public function __construct(string $keyName, string $attributeKeyName = null)
	{
		$this->keyName = $keyName;
		$this->attributeKeyName = $attributeKeyName ?? Str::contains($this->keyName, '.')
				? trim(explode('.', $this->keyName)[1], '\'"')
				: $this->keyName;
	}

	/**
	 * We want to protect these values from external changes. But read is allowed protected attributes.
	 */
	public function __get($name)
	{
		return $this->{$name};
	}

	public static function defaultInstance(): self
	{
		return new self('id');
	}
}
