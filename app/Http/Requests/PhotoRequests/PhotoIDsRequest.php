<?php

namespace App\Http\Requests\PhotoRequests;

use App\Rules\ModelIDListRule;
use Illuminate\Foundation\Http\FormRequest;

class PhotoIDsRequest extends FormRequest
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
		return ['photoIDs' => ['required', new ModelIDListRule()]];
	}
}
