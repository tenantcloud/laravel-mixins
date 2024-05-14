<?php

namespace TenantCloud\Mixins\Mixins;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Tests\ArrMixin\ArrDotWithDepthTest;
use Tests\ArrMixin\ArrSelectTest;

/**
 * @mixin Arr
 */
class ArrMixin
{
	/**
	 * Replace value key's value if it exists.
	 */
	public static function replace(): callable
	{
		return function (array $data, string $key, $valueOrCallback): array {
			// If array has that key, replace it. Otherwise do nothing.
			if (Arr::has($data, $key)) {
				// If callback passed, call it with previous value.
				if (is_callable($valueOrCallback)) {
					$valueOrCallback = $valueOrCallback(Arr::get($data, $key));
				}

				// Set the new value.
				Arr::set($data, $key, $valueOrCallback);
			}

			return $data;
		};
	}

	/**
	 * @see ArrSelectTest
	 *
	 * Selects specified keys from the input array
	 * using dot notation and wildcards. Example:
	 *
	 * $keys = [
	 * 		'id',
	 * 		'roommate.userClient.avatar',
	 * 		'roommate.userClient.realUser.settings.some_setting',
	 * 		'leases.*.roommates.*.tenant.userClient'
	 * ]
	 */
	public function selectWildcard(): callable
	{
		return static function ($array, array $keys, bool $keysAlreadyParsed = false) {
			$result = [];

			// This is an optimization. If you need to iterate over
			// a collection of objects, all having the same $keys,
			// you don't want to parse them each iteration.
			$keys = $keysAlreadyParsed ? $keys : Arr::groupByWildcard($keys);

			// Iterate fields that we need
			foreach ($keys as $key => $group) {
				// If there's no such field in our dataset - just ignore it.
				if (!Arr::has($array, $key)) {
					continue;
				}

				// Get the value from dataset.
				$value = Arr::get($array, $key);

				// If the key has a wildcard after and
				// our dataset value is iterable - map.
				if ($group !== null && $group !== [null] && is_iterable($value)) {
					$mapper = static fn ($item) => Arr::selectWildcard($item, $group);

					// array_map doesn't work with Collections and
					// I don't want to leave type of iterable the same.
					if ($value instanceof Collection) {
						$value = $value->map($mapper);
					} else {
						$value = array_map($mapper, $value);
					}
				}

				Arr::set($result, $key, $value);
			}

			return $result;
		};
	}

	/**
	 * Same as Arr::dot, but it puts
	 * results into array values, not keys.
	 */
	public function dotValues(): callable
	{
		return static function (array $array, $prepend = ''): array {
			$result = [];

			foreach ($array as $key => $value) {
				if (is_array($value) && !empty($value)) {
					$result = array_merge($result, Arr::dotValues($value, $prepend . $key . '.'));
				} else {
					$value = is_array($value) ? $key : $value;

					$result[] = $prepend . $value;
				}
			}

			return $result;
		};
	}

	/**
	 * Groups together dot.notated keys by wildcard symbols in them. Example:
	 *
	 * $keys = [
	 * 		'something',
	 * 		'wildcard.*',
	 * 		'else.*.somekey',
	 * 		'else.*.other.*',
	 * 		'else.*.other_two.*.key'
	 * ];
	 *
	 * $result = [
	 * 		'something' => [null],
	 * 		'wildcard' => [null],
	 * 		'else' => [
	 * 			'somekey' => [null],
	 * 			'other' => [null],
	 * 			'other_two' => [
	 * 				'key'
	 * 			],
	 * 		],
	 * ];
	 */
	public function groupByWildcard(): callable
	{
		return static function ($keys): array {
			return Collection::wrap($keys)
				->mapToGroups(static function ($key) {
					// Find index of first occurrence of `*` in the $field.
					$wildcardIndex = mb_strpos($key, '*');

					// If no wildcard found in the key, just return it.
					if ($wildcardIndex === false) {
						return [$key => null];
					}

					$keyLength = mb_strlen($key);

					// If not followed by anything, strip it and return the key.
					if ($keyLength === $wildcardIndex + 1) {
						// Strip last `.*`
						$key = mb_substr($key, 0, $wildcardIndex - 1);

						return [$key => null];
					}

					// Get key part before `.*.`
					$keyBeforeWildcard = mb_substr($key, 0, $wildcardIndex - 1);

					// Get key part after `.*.`
					$keyAfterWildcard = mb_substr($key, $wildcardIndex + 2);

					return [$keyBeforeWildcard => $keyAfterWildcard];
				})
				->toArray();
		};
	}

	/**
	 * Creates an array from iterable.
	 */
	public function fromIterable(): callable
	{
		return static function ($iterable): array {
			if (is_array($iterable)) {
				return $iterable;
			}

			if ($iterable instanceof Collection) {
				return $iterable->all();
			}

			if (!is_iterable($iterable)) {
				return Arr::wrap($iterable);
			}

			return iterator_to_array($iterable);
		};
	}

	/**
	 * Merges arrays in a similar fashion to array_merge, but does NOT override array values - they are merged instead.
	 *
	 * Example:
	 * $arrayOne = [
	 * 		'item1' => false,
	 * 		'item2' => true,
	 *		'item_list' => [
	 * 			'sub_item1' => 5,
	 * 			'sub_itemlist' => [
	 * 				'sub_sub_item1' => 27,
	 * 			],
	 * 		]
	 * ];
	 *
	 * $arrayTwo = [
	 *		'item_list' => [
	 * 			'sub_item2' => 5,
	 * 			'sub_itemlist' => [
	 * 				'sub_sub_item2' => 27,
	 * 			],
	 * 		],
	 * 		'item3' => true,
	 * ];
	 *
	 * $result = [
	 * 		'item1' => false,
	 * 		'item2' => true,
	 * 		'item_list' => [
	 *			'sub_item1' => 5,
	 * 			'sub_itemlist' => [
	 * 				'sub_sub_item1' => 27,
	 * 				'sub_sub_item2' => 27,
	 *			 ],
	 * 			'sub_item2' => 5,
	 * 		],
	 * 		'item3' => true,
	 * ];
	 */
	public function recursiveDeepMerge(): callable
	{
		return static function (...$arrays): array {
			$result = [];

			foreach ($arrays as $array) {
				foreach ($array as $key => $value) {
					// Renumber integer keys as array_merge_recursive() does. Note that PHP
					// automatically converts array keys that are integer strings (e.g., '1')
					// to integers.
					if (is_int($key)) {
						$result[] = $value;

						continue;
					}

					if (isset($result[$key]) && is_array($result[$key]) && is_array($value)) {
						$result[$key] = Arr::recursiveDeepMerge($result[$key], $value);

						continue;
					}

					$result[$key] = $value;
				}
			}

			return $result;
		};
	}

	/**
	 * Pluck values from array with objects that have value function
	 */
	public function pluckValues(): callable
	{
		return static function (array $array): array {
			$result = [];

			foreach ($array as $key => $value) {
				if (!method_exists($value, '__toString')) {
					continue;
				}

				$result[] = (string) $value;
			}

			return $result;
		};
	}

	/**
	 * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
	 * keys to arrays rather than overwriting the value in the first array with the duplicate
	 * value in the second array, as array_merge does. I.e., with array_merge_recursive,
	 * this happens (documented behavior):
	 *
	 * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
	 *     => array('key' => array('org value', 'new value'));
	 *
	 * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
	 * Matching keys' values in the second array overwrite those in the first array, as is the
	 * case with array_merge, i.e.:
	 *
	 * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
	 *     => array('key' => array('new value'));
	 *
	 * @see https://www.php.net/manual/en/function.array-merge-recursive.php
	 */
	public function recursiveDistinctMerge(): callable
	{
		return static function (array $array1, array $array2): array {
			$merged = $array1;

			foreach ($array2 as $key => &$value) {
				if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
					$merged[$key] = Arr::recursiveDistinctMerge($merged[$key], $value);
				} else {
					$merged[$key] = $value;
				}
			}

			return $merged;
		};
	}

	/**
	 * Gets all of the array values recursively.
	 */
	public function recursiveValues(): callable
	{
		return static function (array $array): array {
			return array_reduce($array, static function (array $carry, $value) {
				$result = is_array($value) ?
					Arr::recursiveValues($value) :
					[$value];

				return $carry + $result;
			}, []);
		};
	}

	/**
	 * Same as Arr::dot, but it has a depth limit.
	 *
	 * @see ArrDotWithDepthTest
	 */
	public function dotWithDepth(): callable
	{
		return static function (array $array, $prepend = '', ?int $depth = null): array {
			$results = [];

			foreach ($array as $key => $value) {
				if (is_array($value) && !empty($value) && ($depth === null || ($depth - 1) > 0)) {
					$results = array_merge($results, Arr::dotWithDepth($value, $prepend . $key . '.', $depth - 1));
				} else {
					$results[$prepend . $key] = $value;
				}
			}

			return $results;
		};
	}
}
