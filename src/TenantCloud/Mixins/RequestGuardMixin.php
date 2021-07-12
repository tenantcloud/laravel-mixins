<?php

namespace TenantCloud\Mixins;

use Illuminate\Auth\RequestGuard;

/**
 * @mixin RequestGuard
 */
class RequestGuardMixin
{
	/**
	 * Logout currently authenticated user.
	 */
	public function logout(): callable
	{
		return function () {
			$this->user = null;
		};
	}
}
