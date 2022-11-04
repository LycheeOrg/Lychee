<?php

namespace App\Http\Requests\Settings;

use App\Http\Requests\BaseApiRequest;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * @mixin Request
 */
abstract class AbstractSettingRequest extends BaseApiRequest
{
	protected string|int|bool $value;

	protected string $name;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(SettingsPolicy::CAN_EDIT, Configs::class);
	}

	public function getSettingName(): string
	{
		return $this->name;
	}

	public function getSettingValue(): string
	{
		return $this->value;
	}
}
