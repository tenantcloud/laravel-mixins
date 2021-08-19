<?php

namespace TenantCloud\Mixins;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\LazyCollection;
use InvalidArgumentException;
use Tests\QueryBuilderMixin\QueryBuilderLazyTest;

/**
 * @mixin Builder
 */
class QueryBuilderMixin
{
	/**
	 * Same as ->toSql(), but with bindings. For debugging purposes.
	 */
	public function toRawSql(): callable
	{
		return fn () => array_reduce($this->getBindings(), static fn ($sql, $binding) => preg_replace('/\?/', is_numeric($binding) ? $binding : "'" . $binding . "'", $sql, 1), $this->toSql());
	}

	/**
	 * Selects this kind of query:
	 *
	 * (CASE
	 * 		WHEN $column IN( $groupedIdsIterator->value ) THEN $groupedIdsIterator->key
	 * 		WHEN $column IN( $groupedIdsIterator->value ) THEN $groupedIdsIterator->key
	 * 		WHEN $column IN( $groupedIdsIterator->value ) THEN $groupedIdsIterator->key
	 * 		WHEN $column IN( $groupedIdsIterator->value ) THEN $groupedIdsIterator->key
	 * END) as $alias
	 */
	public function selectCaseIn(): callable
	{
		return function ($column, $groupedIds, $alias) {
			$whens = [];
			$bindings = [];

			$grammar = $this->getGrammar();

			foreach ($groupedIds as $aliasId => $ids) {
				if (empty($ids)) {
					continue;
				}

				// Create a "?, ?, ?, ?" string with the
				// amount of ? matching count of $ids
				$placeholders = $grammar->parameterize($ids);

				$when = new Expression($column . ' in(' . $placeholders . ')');

				$whens[] = ['key' => $when, 'value' => '?'];
				$bindings = array_merge($bindings, $ids);
				$bindings[] = $aliasId;
			}

			$this
				->selectRaw('(' . $grammar->compileCase($whens) . ') as ' . $alias)
				->addBinding($bindings, 'select');

			return $this;
		};
	}

	/**
	 * Backport of Laravel's lazy. We can't update Laravel due to breaking changes for now.
	 *
	 * @see https://github.com/laravel/framework/pull/36699/files#diff-247adb11c1777e45143ee82ce39b4808c7ea072232c1a6bb1bc874c44f22f013R170
	 * @see QueryBuilderLazyTest
	 */
	public function lazy(): callable
	{
		return function (int $chunkSize = 1000) {
			if ($chunkSize < 1) {
				throw new InvalidArgumentException('The chunk size should be at least 1');
			}

			$this->enforceOrderBy();

			return LazyCollection::make(function () use ($chunkSize) {
				$page = 1;

				while (true) {
					$results = $this->forPage($page++, $chunkSize)->get();

					foreach ($results as $result) {
						yield $result;
					}

					if ($results->count() < $chunkSize) {
						return;
					}
				}
			});
		};
	}
}
