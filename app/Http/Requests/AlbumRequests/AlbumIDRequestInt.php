<?php

namespace App\Http\Requests\AlbumRequests;

use Illuminate\Foundation\Http\FormRequest;

class AlbumIDRequestInt extends FormRequest
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
			'albumID' => 'required|integer',
		];
	}
}
