<?php

namespace App\Http\Requests\AlbumRequests;

use App\Factories\AlbumFactory;
use Illuminate\Foundation\Http\FormRequest;

class AlbumIDsRequest extends FormRequest
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
	public function rules()
	{
		return [
			'albumID' => [
				'required',
				'string',
				function (string $attribute, $value, $fail) {
					$albumIDs = explode(',', $value);
					$success = true;
					foreach ($albumIDs as $albumID) {
						if (
							!is_numeric($albumID) &&
							!array_key_exists($albumID, AlbumFactory::BUILTIN_SMARTS)
						) {
							$success = false;
							break;
						}
					}
					if (!$success) {
						$fail(
							$attribute .
							' must be a comma-seperated string of numeric values or the built-in IDs ' .
							implode(', ', array_keys(AlbumFactory::BUILTIN_SMARTS))
						);
					}
				},
			],
		];
	}
}
