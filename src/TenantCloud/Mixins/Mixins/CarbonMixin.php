<?php

namespace TenantCloud\Mixins\Mixins;

use Carbon\Carbon;

/**
 * @mixin Carbon
 */
class CarbonMixin
{
	/**
	 * {@see Carbon::hasFormat()}, but for ISO 8601 format - ({@see Carbon::toISOString()}.
	 */
	public function hasISOFormat(): callable
	{
		return static fn ($date): bool => Carbon::hasFormat($date, 'Y-m-d\TH:i:s.u\Z');
	}
}
