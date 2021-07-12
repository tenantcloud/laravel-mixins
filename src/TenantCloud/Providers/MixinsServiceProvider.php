<?php

namespace TenantCloud\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
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
	}
}
