<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2;

use Tests\Feature_v2\Base\BaseApiWithDataTest;

class OpenApiTest extends BaseApiWithDataTest
{
	/**
	 * Test Languages are complete.
	 *
	 * @return void
	 */
	public function testOpenApi(): void
	{
		// We check one of the version from the xpaths cross product
		$response = $this->get('/docs/api');
		$response->assertOk();
	}
}
