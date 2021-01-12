<?php

namespace App\Http\Requests\PhotoRequests;

use Illuminate\Foundation\Http\FormRequest;

class PhotoIDRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
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
			'photoID' => 'required|string',
		];
	}
}
