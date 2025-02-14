<?php

namespace App\Http\Middleware\Checks;

use App\Contracts\InternalLycheeException;
use App\Contracts\MiddlewareCheck;
use App\Exceptions\Internal\FrameworkException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class IsInstalled implements MiddlewareCheck
{
	/**
	 * @throws InternalLycheeException
	 */
	public function assert(): bool
	{
		try {
			return
				config('app.key') !== null &&
				config('app.key') !== '' &&
				Schema::hasTable('configs');
		} catch (QueryException $e) {
			// Authentication to DB failled.
			// This means that we cannot even check that `configs` is present,
			// therefore we will just assume it is not.
			//
			// This can only happen if:
			// - Connection with DB is broken (firewall?)
			// - Connection with DB is not set (MySql without credentials)
			//
			// We only check Authentication to DB failled and just skip in
			// the other cases to get a proper message error.
			if (Str::contains($e->getMessage(), 'SQLSTATE[HY000] [1045]')) {
				return false;
			}
			throw $e;
		} catch (BindingResolutionException|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		}
	}
}