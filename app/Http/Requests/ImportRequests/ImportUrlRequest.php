<?php

namespace App\Http\Requests\ImportRequests;

use App\Rules\AlbumIDRule;
use Illuminate\Foundation\Http\FormRequest;

class ImportUrlRequest extends FormRequest
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
			'url' => 'string|required',
			'albumID' => ['required', new AlbumIDRule()],
		];
	}
}
