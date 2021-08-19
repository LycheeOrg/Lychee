<?php

namespace App\Http\Requests\AlbumRequests;

use App\Rules\AlbumIDRule;
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
		return ['albumID' => ['required', new AlbumIDRule()]];
	}
}
