<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Diagnostics\Configuration;
use App\Actions\Diagnostics\Errors;
use App\Actions\Diagnostics\Info;
use App\Actions\Diagnostics\Space;
use App\Http\Requests\Diagnostics\DiagnosticsRequest;
use App\Http\Resources\Diagnostics\ErrorLine;
use Illuminate\Routing\Controller;

class DiagnosticsController extends Controller
{
	public function errors(Errors $errors)
	{
		return collect($errors->get())->map(fn ($line) => new ErrorLine($line))->all();
	}

	public function space(DiagnosticsRequest $_request, Space $space)
	{
		return $space->get();
	}

	public function info(DiagnosticsRequest $_request, Info $info)
	{
		return $info->get();
	}

	public function config(DiagnosticsRequest $_request, Configuration $config)
	{
		return $config->get();
	}
}
