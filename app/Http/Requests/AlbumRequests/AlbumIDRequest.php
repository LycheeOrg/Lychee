<?php

namespace App\Http\Requests\AlbumRequests;

use App\Factories\SmartFactory;
use Illuminate\Foundation\Http\FormRequest;

class AlbumIDRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		return [
			'albumID' => [
				'required',
				function (string $attribute, $value, $fail) {
					if (
						$value !== null &&
						!is_numeric($value) &&
						!array_key_exists($value, SmartFactory::BASE_SMARTS)
					) {
						$fail(
							$attribute .
							' must either be null, a numeric value or one of the pre-determined IDs ' .
							implode(', ', array_keys(SmartFactory::BASE_SMARTS))
						);
					}
				},
			],
		];
	}
}
