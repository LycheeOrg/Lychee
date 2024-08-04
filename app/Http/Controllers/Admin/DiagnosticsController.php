<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Diagnostics\Configuration;
use App\Actions\Diagnostics\Errors;
use App\Actions\Diagnostics\Info;
use App\Actions\Diagnostics\Space;
use App\Http\Requests\Diagnostics\DiagnosticsRequest;
use App\Http\Resources\Diagnostics\ErrorsResource;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Controller;

class DiagnosticsController extends Controller
{
	/**
	 * Display the errors detected in Lychee.
	 *
	 * @param Errors $errors
	 *
	 * @return ErrorsResource
	 *
	 * @throws BindingResolutionException
	 */
	public function errors(Errors $errors): ErrorsResource
	{
		return new ErrorsResource($errors->get());
	}

	/**
	 * Get the space usage.
	 * ! This is slow.
	 *
	 * @param DiagnosticsRequest $_request
	 * @param Space              $space
	 *
	 * @return string[]
	 */
	public function space(DiagnosticsRequest $_request, Space $space)
	{
		return $space->get();
	}

	/**
	 * Get info of the installation.
	 *
	 * @param DiagnosticsRequest $_request
	 * @param Info               $info
	 *
	 * @return string[]
	 */
	public function info(DiagnosticsRequest $_request, Info $info): array
	{
		return $info->get();
	}

	/**
	 * Get the configuration of the installation.
	 *
	 * @param DiagnosticsRequest $_request
	 * @param Configuration      $config
	 *
	 * @return string[]
	 */
	public function config(DiagnosticsRequest $_request, Configuration $config): array
	{
		return $config->get();
	}
}
