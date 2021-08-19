<?php

namespace Tests\Database\Models;

use Illuminate\Database\Eloquent\Model;

class TestStub extends Model
{
	protected $table = 'test_stubs';

	protected $fillable = [
		'name',
	];
}
