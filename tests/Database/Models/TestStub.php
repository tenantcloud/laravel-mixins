<?php

namespace Tests\Database\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int    $parent_id
 * @property string $name
 * @property Carbon $updated_at
 */
class TestStub extends Model
{
	protected $table = 'test_stubs';

	protected $fillable = [
		'name',
	];

	/**
	 * @return HasMany<self>
	 */
	public function children(): HasMany
	{
		return $this->hasMany(self::class, 'parent_id');
	}
}
