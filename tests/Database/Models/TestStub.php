<?php

namespace Tests\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestStub extends Model
{
	protected $table = 'test_stubs';

	protected $fillable = [
		'name',
	];

	public function children(): HasMany
	{
		return $this->hasMany(self::class, 'parent_id');
	}
}
