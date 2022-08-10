<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\Database\Models\TestStub;

class CreateTestStubsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('test_stubs', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->foreignIdFor(TestStub::class, 'parent_id')
				->nullable();
			$table->string('name');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('test_stubs');
	}
}
