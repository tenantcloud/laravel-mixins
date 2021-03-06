<?php

namespace TenantCloud\Mixins\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\Services\CacheSerializerTest;

/**
 * @see CacheSerializerTest
 */
class CacheSerializer
{
	public static function serialize($object, callable $serializeCallback = null): array
	{
		$serializeCallback ??= fn ($object) => serialize($object);
		$serialized = $serializeCallback($object);

		// todo: migrate to murmurhash3 once this requires php >= 8.1
		if (
			!Cache::has($key = hash('sha256', $serialized)) ||
			Carbon::createFromTimestamp(Cache::get($key . ':lifetime'))->subDay()->isPast()
		) {
			$lifetime = Carbon::now()->addWeek();
			Cache::put($key, $serialized, $lifetime);
			Cache::put($key . ':lifetime', $lifetime->getTimestamp(), $lifetime);
		}

		return ['key' => $key];
	}

	public static function unserialize(array $data, callable $unserializeCallback = null)
	{
		$unserializeCallback ??= fn (string $data) => unserialize($data);

		return $unserializeCallback(Cache::get($data['key']));
	}
}
