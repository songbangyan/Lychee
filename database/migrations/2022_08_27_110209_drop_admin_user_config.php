<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class DropAdminUserConfig extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::table('configs')
			->whereIn('key', ['username', 'password'])
			->delete();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		defined('STRING_REQ') or define('STRING_REQ', 'string_required');

		DB::table('configs')->insert([
			[
				'key' => 'username',
				'value' => '',
				'confidentiality' => '4',
				'cat' => 'Admin',
				'type_range' => STRING_REQ,
			],
			[
				'key' => 'password',
				'value' => '',
				'confidentiality' => '4',
				'cat' => 'Admin',
				'type_range' => STRING_REQ,
			],
		]
		);
	}
}
