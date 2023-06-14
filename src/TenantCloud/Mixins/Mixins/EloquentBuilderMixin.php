<?php

namespace TenantCloud\Mixins\Mixins;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use TenantCloud\Mixins\Jobs\ChunkParams;
use TenantCloud\Mixins\Jobs\GenerateChunksJob;
use TenantCloud\Mixins\Jobs\SerializableBuilder;
use TenantCloud\Mixins\Queue\Handlers\Contracts\QueuedChunkHandler;
use TenantCloud\Mixins\Queue\Handlers\Serializable\ChunkHandler;
use TenantCloud\Mixins\Queue\Handlers\Serializable\Handler;
use TenantCloud\Mixins\Queue\Handlers\Serializable\ItemHandler;
use TenantCloud\Mixins\Settings\ChunkWithQueue\ChunkWithQueueSettings;
use Tests\EloquentBuilderMixin\ChunkWithQueueTest;
use Webmozart\Assert\Assert;

/**
 * @mixin Builder
 */
class EloquentBuilderMixin extends QueryBuilderMixin
{
	/**
	 * Same as "->withCount() > 0", but a lot more optimized, as it uses EXISTS clause instead of COUNT().
	 *
	 * Example usage:
	 *
	 * $query->withHas('properties')
	 * 		->withHas(['properties as has_cool_properties' => static function ($query) {
	 * 			return $query->where('is_cool', true);
	 * 		});
	 */
	public function withHas(): callable
	{
		return function ($relations) {
			if (empty($relations)) {
				return $this;
			}

			if ($this->query->columns === null) {
				$this->query->select([$this->query->from . '.*']);
			}

			$relations = is_array($relations) ? $relations : func_get_args();

			foreach ($this->parseWithRelations($relations) as $name => $constraints) {
				// First we will determine if the name has been aliased using an "as" clause on the name
				// and if it has we will extract the actual relationship name and the desired name of
				// the resulting column. This allows multiple counts on the same relationship name.
				$segments = explode(' ', $name);

				unset($alias);

				if (count($segments) === 3 && Str::lower($segments[1]) === 'as') {
					[$name, $alias] = [$segments[0], $segments[2]];
				}

				$relation = $this->getRelationWithoutConstraints($name);

				// Here we will get the relationship existence query and prepare to add it to the main query
				// as a sub-select. First, we'll get the "has" query and use that to get the relation
				// count query. We will normalize the relation name then append _has as the name.
				$query = $relation->getRelationExistenceQuery(
					$relation->getRelated()->newQuery(),
					$this
				)->setBindings([], 'select');

				$query->callScope($constraints);

				$query = $query->mergeConstraintsFrom($relation->getQuery())->toBase();

				if (count($query->columns) > 1) {
					$query->columns = [$query->columns[0]];
				}

				// Finally we will add the proper result column alias to the query and run the subselect
				// statement against the query builder. Then we will return the builder instance back
				// to the developer for further constraint chaining that needs to take place on it.
				$column = $alias ?? Str::snake('has_' . $name);

				$column = $this->getQuery()->getGrammar()->wrap($column);

				$sql = 'exists(' . $query->toSql() . ') as ' . $column;

				return $this->selectRaw($sql, $query->getBindings());
			}

			return $this;
		};
	}

	/**
	 * Same as ->withCount(), except it allows to use custom aggregate, such as sum(income).
	 *
	 * Example usage:
	 *
	 * $query->withAggregate([
	 * 		'incomes as incomes_sum',
	 * 		'employments as employment_incomes_sum' => static function ($query) {
	 * 			return $query->where('is_current', true);
	 * 		},
	 * ], new Expression('sum(income)'));
	 */
	public function withAggregate(): callable
	{
		return function ($relations, Expression $aggregateExpression) {
			if (empty($relations)) {
				return $this;
			}

			if ($this->query->columns === null) {
				$this->query->select([$this->query->from . '.*']);
			}

			$relations = Arr::wrap($relations);

			foreach ($this->parseWithRelations($relations) as $name => $constraints) {
				// First we will determine if the name has been aliased using an "as" clause on the name
				// and if it has we will extract the actual relationship name and the desired name of
				// the resulting column. This allows multiple counts on the same relationship name.
				$segments = explode(' ', $name);

				if (!(count($segments) === 3 && Str::lower($segments[1]) === 'as')) {
					throw new InvalidArgumentException('Alias for relation aggregate must be defined.');
				}

				[$name, $alias] = [$segments[0], $segments[2]];

				$relation = $this->getRelationWithoutConstraints($name);

				// Here we will get the relationship count query and prepare to add it to the main query
				// as a sub-select. First, we'll get the "has" query and use that to get the relation
				// count query. We will normalize the relation name then append _count as the name.
				$query = $relation->getRelationExistenceQuery(
					$relation->getRelated()->newQuery(),
					$this,
					$aggregateExpression
				)->setBindings([], 'select');

				$query->callScope($constraints);

				$query = $query->mergeConstraintsFrom($relation->getQuery())->toBase();

				if (count($query->columns) > 1) {
					$query->columns = [$query->columns[0]];
				}

				$this->selectSub($query, $alias);
			}

			return $this;
		};
	}

	/**
	 * Alias for calculating sum using ->withAggregate().
	 *
	 * Example usage:
	 *
	 * $query->withSum('incomes as income_sum', 'income');
	 */
	public function withSum(): callable
	{
		return fn ($relations, string $column) => $this->withAggregate($relations, new Expression("coalesce(sum({$column}), 0)"));
	}

	/**
	 * Used for mass update with switch cases
	 */
	public function conditionalUpdate(): callable
	{
		return function (string $changedField, string $caseField, array $cases) {
			$this->whereIn($caseField, array_keys($cases));

			$query = $this->getQuery();

			$caseSql = $query->grammar->compileCase($cases, $caseField);

			$sql = $query->grammar->compileUpdate($query, [
				$changedField => new Expression($caseSql),
			]);

			$bindings = collect($cases)
				->flatMap(static fn ($value, $key) => [$key, $value])
				->all();

			return $query->connection->update(
				$sql,
				$query->grammar->prepareBindingsForUpdate($query->bindings, $bindings)
			);
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
			$this->getQuery()
				->customWhereNested(fn () => $apply($this), $boolean);

			return $this;
		};
	}

	/**
	 * Passthru for this mixin.
	 *
	 * {@inheritDoc}
	 */
	public function selectCaseIn(): callable
	{
		return function ($column, $groupedIds, $alias) {
			$this->getQuery()->selectCaseIn($column, $groupedIds, $alias);

			return $this;
		};
	}

	/**
	 * Same as chunkById for large tables. Split table into small piece and fire @see QueuedChunkHandler job for each of them.
	 *
	 * Example usage:
	 *
	 * $query->chunkWithQueue(ChunkWorkerContract::class, $settings)
	 *
	 * @see ChunkWithQueueTest
	 */
	public function chunkWithQueue(): callable
	{
		/* @param string|callable|ChunkHandler|ItemHandler $handler */
		return function (
			$handler,
			ChunkWithQueueSettings $settings = null
		) {
			if (!$settings) {
				$settings = ChunkWithQueueSettings::defaultSettings();
			}

			$handler = is_object($handler) && is_a($handler, Handler::class) ? $handler : new ChunkHandler($handler);

			/* @var Builder $query */
			$query = clone $this;

			$maxKeyValue = optional(
				DB::query()
					->fromSub(
						$this->clone()
							->toBase()
							->orderBy($settings->queryOptions->keyName, 'desc')
							->limit(1),
						$this->getModel()->getTable()
					)
					->first($settings->queryOptions->keyName)
			)->{$settings->queryOptions->attributeKeyName};

			if (!$maxKeyValue) {
				return true;
			}

			$maxChunkNumber = (int) ceil($maxKeyValue / $settings->chunkOptions->pieceSize);

			$params = new ChunkParams(
				$handler,
				$settings->queryOptions->keyName,
				$settings->queryOptions->attributeKeyName,
				$settings->chunkOptions->chunkSize,
				$settings->chunkOptions->pieceSize,
				$settings->queueOptions->chunkQueue
			);

			$builder = new SerializableBuilder($query);

			for ($chunkNumber = 1; $chunkNumber <= $maxChunkNumber; $chunkNumber++) {
				dispatch(new GenerateChunksJob($builder, $params, $chunkNumber))
					->onQueue($settings->queueOptions->pieceQueue)
					->delay($settings->queueOptions->delay ? $settings->queueOptions->delay * ($chunkNumber - 1) : null);
			}

			return true;
		};
	}

	/**
	 * Similar to {@see Builder::setModel()}, but only sets the model, without the table.
	 */
	public function setOnlyModel(): callable
	{
		return function (Model $model) {
			$this->model = $model;

			return $this;
		};
	}
}
