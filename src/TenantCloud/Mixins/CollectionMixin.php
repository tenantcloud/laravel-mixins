<?php

namespace TenantCloud\Mixins;

use Illuminate\Support\Collection;
use Tests\CollectionMixin\CollectionSortByMultiTest;
use Tests\CollectionMixin\CollectionUngroupTest;

/**
 * @mixin Collection
 */
class CollectionMixin
{
	/**
	 * Intersect given custom callback.
	 */
	public function intersectCustom(): callable
	{
		return fn ($items, callable $compare): Collection => new Collection(array_uintersect($this->all(), $this->getArrayableItems($items), $compare));
	}

	/**
	 * Ungroup a previously grouped collection (grouped by {@see Collection::groupBy()})
	 *
	 * @see CollectionUngroupTest
	 */
	public function ungroup(): callable
	{
		return function (): Collection {
			// create a new collection to use as the collection where the other collections are merged into
			$newCollection = Collection::make([]);

			$this->each(function ($item) use (&$newCollection) {
				// use merge to combine the collections
				$newCollection = $newCollection->merge($item);
			});

			return $newCollection;
		};
	}

	/**
	 * An extension of the {@see Collection::sortBy()} method that allows for sorting against as many different
	 * keys. Uses a combination of {@see Collection::sortBy()} and {@see Collection::groupBy()} to achieve this.
	 *
	 * @param array $keys An associative array that uses the key to sort by (which accepts dot separated values,
	 *                    as {@see Collection::sortBy()} would) and the value is the order (either ASC or DESC)
	 *
	 * @see CollectionSortByMultiTest
	 */
	public function sortByMulti(): callable
	{
		return function (array $keys): Collection {
			$currentIndex = 0;

			$keys = array_map(function ($key, $sort) {
				if (is_callable($sort)) {
					$key = $sort;
				}

				return ['key' => $key, 'sort' => $sort];
			}, array_keys($keys), $keys);

			$sortBy = function (Collection $collection) use (&$currentIndex, $keys, &$sortBy) {
				if ($currentIndex >= count($keys)) {
					return $collection;
				}

				$key = $keys[$currentIndex]['key'];
				$sort = $keys[$currentIndex]['sort'];
				$sortFunc = $sort === 'DESC' ? 'sortByDesc' : 'sortBy';
				$currentIndex++;

				return $collection->{$sortFunc}($key)->groupBy($key)->map($sortBy)->ungroup();
			};

			return $sortBy($this);
		};
	}
}
