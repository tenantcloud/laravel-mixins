<?php

namespace TenantCloud\Mixins\Mixins;

use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Tests\HasOneOrManyMixin\DisassociateTest;

/**
 * @mixin HasOneOrMany
 */
class HasOneOrManyMixin
{
	/**
	 * Disassociates a nullable relation by setting the foreign key name to null.
	 *
	 * @see DisassociateTest
	 */
	public function disassociate(): callable
	{
		return function () {
			$this->update([
				$this->getQualifiedForeignKeyName() => null,
			]);

			return $this;
		};
	}
}
