<?php

declare(strict_types=1);

namespace App\Http\Requests\Logs;

use App\Http\Requests\AbstractEmptyRequest;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;

class ShowJobsRequest extends AbstractEmptyRequest
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(SettingsPolicy::CAN_SEE_LOGS, Configs::class);
	}
}
