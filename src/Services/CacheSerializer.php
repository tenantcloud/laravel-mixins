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
	/**
	 * @template TObject
	 *
	 * @param TObject                       $object
	 * @param callable(TObject):string|null $serializeCallback
	 *
	 * @return array{ key: string }
	 */
	public static function serialize(mixed $object, ?callable $serializeCallback = null): array
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

	/**
	 * @template TObject
	 *
	 * @param array{ key: string }          $data
	 * @param callable(string):TObject|null $unserializeCallback
	 *
	 * @return TObject
	 */
	public static function unserialize(array $data, ?callable $unserializeCallback = null): mixed
	{
		$unserializeCallback ??= fn (string $data) => unserialize($data);

		return $unserializeCallback(Cache::get($data['key']));
	}
}
