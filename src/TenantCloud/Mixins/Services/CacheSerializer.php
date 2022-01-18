<?php

namespace TenantCloud\Mixins\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class CacheSerializer
{
	public static function serialize(object $object, callable $serializeCallback = null): array
	{
		$serializeCallback ??= fn ($object) => serialize($object);
		Cache::put($key = uniqid('mixins:serialized:', false), $serializeCallback($object), Carbon::now()->addWeek());

		return ['key' => $key];
	}

	public static function unserialize(array $data, callable $unserializeCallback = null)
	{
		$unserializeCallback ??= fn (string $data) => unserialize($data);

		return $unserializeCallback(Cache::get($data['key']));
	}
}
