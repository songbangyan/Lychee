<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Album;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\Album\AddTagAlbumRequest;
use App\Policies\AlbumPolicy;
use App\Rules\TitleRule;
use Illuminate\Support\Facades\Gate;
use Tests\Unit\Http\Requests\Base\BaseRequestTest;

class AddTagAlbumRequestTest extends BaseRequestTest
{
	public function testAuthorization()
	{
		Gate::shouldReceive('check')
			->with(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, null])
			->andReturn(true);

		$request = new AddTagAlbumRequest();
		$request->merge([RequestAttribute::PARENT_ID_ATTRIBUTE => null]);

		$this->assertTrue($request->authorize());
	}

	public function testRules(): void
	{
		$request = new AddTagAlbumRequest();

		$rules = $request->rules();

		$this->assertArrayHasKey(RequestAttribute::TITLE_ATTRIBUTE, $rules);
		$this->assertArrayHasKey(RequestAttribute::TAGS_ATTRIBUTE, $rules);
		$this->assertArrayHasKey(RequestAttribute::TAGS_ATTRIBUTE . '.*', $rules);

		$this->assertEquals(['required', new TitleRule()], $rules[RequestAttribute::TITLE_ATTRIBUTE]);
		$this->assertEquals('required|array|min:1', $rules[RequestAttribute::TAGS_ATTRIBUTE]);
		$this->assertEquals('required|string|min:1', $rules[RequestAttribute::TAGS_ATTRIBUTE . '.*']);
	}
}