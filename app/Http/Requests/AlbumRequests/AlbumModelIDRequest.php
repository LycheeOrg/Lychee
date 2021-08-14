<?php

namespace App\Http\Requests\AlbumRequests;

use Illuminate\Foundation\Http\FormRequest;

class AlbumModelIDRequest extends FormRequest
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
						(filter_var($value, FILTER_VALIDATE_INT) === false || intval($value) < 0)
					) {
						$fail(
							$attribute . ' must either be null or a positive integer'
						);
					}
				},
			],
		];
	}
}
