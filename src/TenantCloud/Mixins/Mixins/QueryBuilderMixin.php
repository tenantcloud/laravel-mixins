<?php

namespace TenantCloud\Mixins\Mixins;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;

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
}
