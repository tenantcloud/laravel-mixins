<?php

namespace TenantCloud\Mixins\Models;

use Illuminate\Database\Eloquent\Model;

class TestStub extends Model
{
	protected $table = 'test_stubs';

	protected $fillable = [
		'name',
	];
}
