<?php

namespace TenantCloud\Mixins\Mixins;

use Illuminate\Contracts\Auth\Factory;
use Illuminate\Http\Request;

/**
 * @mixin Request
 */
class RequestMixin
{
	/**
	 * Get an instance of auth manager for this request.
	 */
	public function auth(): callable
	{
		return function (): Factory {
			// Implementation is pretty stupid, but it works. Of course, ideally, each Request instance
			// would have had it's own instance of auth manager and auth manager wouldn't be a singleton.
			// But this is an adaption to Laravel realities, giving us an ability to improve internals later on.
			return auth();
		};
	}
}
