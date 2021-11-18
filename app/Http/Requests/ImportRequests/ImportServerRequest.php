<?php

namespace App\Http\Requests\ImportRequests;

use App\Rules\AlbumIDRule;
use Illuminate\Foundation\Http\FormRequest;

class ImportServerRequest extends FormRequest
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
			'path' => 'string|required',
			'albumID' => ['required', new AlbumIDRule()],
			'delete_imported' => 'int',
			'import_via_symlink' => 'int',
			'skip_duplicates' => 'int',
			'resync_metadata' => 'int',
		];
	}
}
