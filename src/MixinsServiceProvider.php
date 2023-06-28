<?php

namespace TenantCloud\Mixins;

use Carbon\Carbon;
use Illuminate\Auth\RequestGuard;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use TenantCloud\Mixins\Mixins\ArrMixin;
use TenantCloud\Mixins\Mixins\CarbonMixin;
use TenantCloud\Mixins\Mixins\CollectionMixin;
use TenantCloud\Mixins\Mixins\EloquentBuilderMixin;
use TenantCloud\Mixins\Mixins\HasOneOrManyMixin;
use TenantCloud\Mixins\Mixins\MySqlGrammarMixin;
use TenantCloud\Mixins\Mixins\QueryBuilderMixin;
use TenantCloud\Mixins\Mixins\RequestGuardMixin;
use TenantCloud\Mixins\Mixins\RequestMixin;
use TenantCloud\Mixins\Mixins\StrMixin;

class MixinsServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 */
	public function boot(): void
	{
		Str::mixin(new StrMixin());
		Arr::mixin(new ArrMixin());
		Carbon::mixin(new CarbonMixin());
		Collection::mixin(new CollectionMixin());
		MySqlGrammar::mixin(new MySqlGrammarMixin());
		QueryBuilder::mixin(new QueryBuilderMixin());
		JoinClause::mixin(new QueryBuilderMixin());
		Request::mixin(new RequestMixin());
		RequestGuard::mixin(new RequestGuardMixin());
		EloquentBuilder::mixin(new EloquentBuilderMixin());
		HasOneOrMany::mixin(new HasOneOrManyMixin());
	}
}
