<?php

namespace TenantCloud\Mixins\Mixins;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use Tests\CollectionMixin\CollectionFullDiffTest;
use Tests\CollectionMixin\CollectionSortByMultiTest;
use Tests\CollectionMixin\CollectionUngroupTest;
use Tests\CollectionMixin\CollectionZipByKeyTest;

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

	/**
	 * Same as {@see Collection::zip()}, but instead of zipping by index, zips by array key.
	 *
	 * @see CollectionZipByKeyTest
	 */
	public function zipByKey(): callable
	{
		return function ($other): Collection {
			/** @var Collection $this */
			$other = Collection::wrap($other);

			return $this->keys()
				->merge($other->keys())
				->unique()
				->values()
				->map(fn ($key) => [$this[$key] ?? null, $other[$key] ?? null]);
		};
	}

	/**
	 * Calculates full difference with the other collection by matching them with keys.
	 *
	 * @see CollectionFullDiffTest
	 */
	public function fullDiff(): callable
	{
		/*
		 * @return array{Collection, Collection, Collection} First - elements in first, not in second collection
		 *                                                   Second - elements in both collection
		 *                                                   Third - elements not in first, but in second collection
		 */
		return function ($other, callable $localKey, ?callable $foreignKey = null): array {
			/** @var Collection $this */
			$other = Collection::wrap($other);
			$foreignKey ??= $localKey;

			$groups = $this
				->keyBy($localKey)
				->zipByKey(
					$other->keyBy($foreignKey)
				)
				->groupBy(function (array $pair) {
					switch (true) {
						case $pair[0] && !$pair[1]:
							return 0;
						case $pair[0] && $pair[1]:
							return 1;
						case !$pair[0] && $pair[1]:
							return 2;

						default:
							throw new InvalidArgumentException();
					}
				})
				->all();

			return [
				($groups[0] ?? new Collection())->map(fn (array $pair) => $pair[0]),
				$groups[1] ?? new Collection(),
				($groups[2] ?? new Collection())->map(fn (array $pair) => $pair[1]),
			];
		};
	}
}
