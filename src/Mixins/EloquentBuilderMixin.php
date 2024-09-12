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

/**
 * @mixin Builder
 */
class EloquentBuilderMixin extends QueryBuilderMixin
{
	/**
	 * @deprecated Use Laravel's {@see Builder::withExists()} instead.
	 *
	 * Same as "->withCount() > 0", but a lot more optimized, as it uses EXISTS clause instead of COUNT().
	 *
	 * Example usage:
	 *
	 * $query->withHas('properties')
	 *        ->withHas(['properties as has_cool_properties' => static function ($query) {
	 *            return $query->where('is_cool', true);
	 *        });
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
	 * @deprecated Use Laravel's {@see Builder::withAggregate()} instead.
	 *
	 * Same as ->withCount(), except it allows to use custom aggregate, such as sum(income).
	 *
	 * Example usage:
	 *
	 * $query->withAggregate([
	 *        'incomes as incomes_sum',
	 *        'employments as employment_incomes_sum' => static function ($query) {
	 *            return $query->where('is_current', true);
	 *        },
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
	 * @deprecated Use Laravel's {@see Builder::withSum()} instead.
	 *
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
	 * @see QueryBuilderMixin::customWhereNested()
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
			$this->getQuery()->customWhereNested(fn () => $apply($this), $boolean);

			return $this;
		};
	}

	/**
	 * @see QueryBuilderMixin::whereSubOther()
	 */
	public function whereSubOther(): callable
	{
		return function (string $column, string $operator, $query, string $boolean = 'and') {
			$this->getQuery()->whereSubOther($column, $operator, $query, $boolean);

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
			?ChunkWithQueueSettings $settings = null
		) {
			if (!$settings) {
				$settings = ChunkWithQueueSettings::defaultSettings();
			}

			$handler = is_object($handler) && is_a($handler, Handler::class) ? $handler : new ChunkHandler($handler);

			/** @var Builder $query */
			$query = clone $this;

			$maxKeyValue = optional(
				DB::query()
					->fromSub(
						($settings->queryOptions->getMaxKeyValueFromNewModelQuery ? $this->getModel()->newQuery() : $this->clone())
							->toBase()
							->orderBy($settings->queryOptions->keyName, 'desc')
							->limit(1),
						$this->getModel()->getTable()
					)
					->first($settings->queryOptions->keyName)
			)->{$settings->queryOptions->attributeKeyName};

			$minKeyValue = optional(
				DB::query()
					->fromSub(
						($settings->queryOptions->getMinKeyValueFromNewModelQuery ? $this->getModel()->newQuery() : $this->clone())
							->toBase()
							->orderBy($settings->queryOptions->keyName)
							->limit(1),
						$this->getModel()->getTable()
					)
					->first($settings->queryOptions->keyName)
			)->{$settings->queryOptions->attributeKeyName} ?? 1;

			if (!$maxKeyValue) {
				return true;
			}

			$maxChunkNumber = (int) ceil($maxKeyValue / $settings->chunkOptions->pieceSize);
			$minChunkNumber = max((int) floor($minKeyValue / $settings->chunkOptions->pieceSize), 1);

			$params = new ChunkParams(
				$handler,
				$settings->queryOptions->keyName,
				$settings->queryOptions->attributeKeyName,
				$settings->chunkOptions->chunkSize,
				$settings->chunkOptions->pieceSize,
				$settings->queueOptions->chunkQueue
			);

			$builder = new SerializableBuilder($query);

			$delayNumber = 1;

			for ($chunkNumber = $minChunkNumber; $chunkNumber <= $maxChunkNumber; $chunkNumber++) {
				dispatch(new GenerateChunksJob($builder, $params, $chunkNumber))
					->onQueue($settings->queueOptions->pieceQueue)
					->delay($settings->queueOptions->delay ? $settings->queueOptions->delay * ($delayNumber - 1) : null);

				$delayNumber++;
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

	/**
	 * @see QueryBuilderMixin::conditionalUpdate()
	 */
	public function conditionalUpdate(): callable
	{
		return fn (string $changedField, string $caseField, array $cases) => $this->getQuery()->conditionalUpdate($changedField, $caseField, $cases);
	}
}
