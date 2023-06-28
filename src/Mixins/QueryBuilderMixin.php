<?php

namespace TenantCloud\Mixins\Mixins;

use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\LazyCollection;
use InvalidArgumentException;
use Tests\QueryBuilderMixin\QueryBuilderCustomWhereNestedTest;
use Tests\QueryBuilderMixin\QueryBuilderLazyTest;
use Tests\QueryBuilderMixin\QueryBuilderWhereSubOtherTest;
use Webmozart\Assert\Assert;

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

	/**
	 * Same as ->whereNested(), except it works with selects, joins, withCounts etc. inside of the callback.
	 *
	 * This idea was stolen from {@see Builder::callScope}.
	 *
	 * The way it works is the following:
	 *  1. It saves ->where clauses that exist BEFORE calling a callback.
	 *  2. It calls a callback.
	 *  3. It detects which ->where clauses were added by the callback
	 *     (given that we know which ones were present before calling the callback)
	 *  4. It deletes added ->where clauses from the query and re-adds them with nesting and given boolean (and/or).
	 *
	 * @see QueryBuilderCustomWhereNestedTest
	 */
	public function customWhereNested(): callable
	{
		/*
		 * @param callable $apply Callback for the query.
		 * @param string $boolean 'and', 'or', 'and not' or 'or not'
		 *
		 * @return Builder
		 */
		return function (callable $apply, string $boolean = 'and'): Builder {
			Assert::oneOf($boolean, ['and', 'or', 'and not', 'or not']);

			// We will keep track of how many wheres are on the query before running the
			// scope so that we can properly group the added scope constraints in the
			// query as their own isolated nested where statement and avoid issues.
			$originalWhereCount = $this->wheres === null ? 0 : count($this->wheres);

			$apply($this);

			// If any new wheres were added..
			if (count((array) $this->wheres) > $originalWhereCount) {
				// Here we'll remove all of the added wheres from the query and write it down to $addedWheres.
				$addedWheres = array_splice($this->wheres, $originalWhereCount);

				// Then we'll create another query, containing those new added wheres.
				$addedWheresFakeQuery = $this->forNestedWhere();
				$addedWheresFakeQuery->wheres = $addedWheres;

				// And tell query builder to add it as "whereNested", basically.
				$this->addNestedWhereQuery($addedWheresFakeQuery, $boolean);
			}

			return $this;
		};
	}

	/**
	 * Same as {@see Builder::whereSub()}, but allows passing the query (with a different table) instead of using a callback.
	 *
	 * @see QueryBuilderWhereSubOtherTest
	 */
	public function whereSubOther(): callable
	{
		return function (string $column, string $operator, $query, string $boolean = 'and') {
			$type = 'Sub';
			$query = $query instanceof EloquentBuilder ? $query->getQuery() : $query;

			$this->wheres[] = compact(
				'type',
				'column',
				'operator',
				'query',
				'boolean'
			);

			$this->addBinding($query->getBindings(), 'where');

			return $this;
		};
	}
}
