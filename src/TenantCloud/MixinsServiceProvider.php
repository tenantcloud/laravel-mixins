<?php

namespace TenantCloud;

use Carbon\Carbon;
use Illuminate\Auth\RequestGuard;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use TenantCloud\Mixins\ArrMixin;
use TenantCloud\Mixins\CarbonMixin;
use TenantCloud\Mixins\CollectionMixin;
use TenantCloud\Mixins\EloquentBuilderMixin;
use TenantCloud\Mixins\MySqlGrammarMixin;
use TenantCloud\Mixins\QueryBuilderMixin;
use TenantCloud\Mixins\RequestGuardMixin;
use TenantCloud\Mixins\RequestMixin;
use TenantCloud\Mixins\StrMixin;

class MixinsServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		Str::mixin(new StrMixin());
		Arr::mixin(new ArrMixin());
		Carbon::mixin(new CarbonMixin());
		Collection::mixin(new CollectionMixin());
		MySqlGrammar::mixin(new MySqlGrammarMixin());
		QueryBuilder::mixin(new QueryBuilderMixin());
		Request::mixin(new RequestMixin());
		RequestGuard::mixin(new RequestGuardMixin());

		// Because Eloquent Builder doesn't work with mixins.
		EloquentBuilder::macro('withHas', (new EloquentBuilderMixin())->withHas());
		EloquentBuilder::macro('withAggregate', (new EloquentBuilderMixin())->withAggregate());
		EloquentBuilder::macro('withSum', (new EloquentBuilderMixin())->withSum());
		EloquentBuilder::macro('selectCaseIn', (new EloquentBuilderMixin())->selectCaseIn());
		EloquentBuilder::macro('customWhereNested', (new EloquentBuilderMixin())->customWhereNested());
		EloquentBuilder::macro('conditionalUpdate', (new EloquentBuilderMixin())->conditionalUpdate());
		EloquentBuilder::macro('toRawSql', (new EloquentBuilderMixin())->toRawSql());
		EloquentBuilder::macro('chunkWithQueue', (new EloquentBuilderMixin())->chunkWithQueue());
	}
}
