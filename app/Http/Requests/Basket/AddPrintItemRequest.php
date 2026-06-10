<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Basket;

use App\Models\Photo;
use App\Models\PrintSize;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;

class AddPrintItemRequest extends BaseBasketRequest
{
	public Photo $photo;
	public string $album_id;
	public PrintSize $print_size;
	public ?string $email = null;
	public ?string $notes = null;

	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool
	{
		return $this->order?->canAddItems() === true && $this->photo->albums()->where('id', $this->album_id)->exists();
	}

	/**
	 * Get the validation rules that apply to the request.
	 */
	public function rules(): array
	{
		return [
			'photo_id' => ['required', 'string'],
			'album_id' => ['required', 'string'],
			'print_size_id' => ['required', 'integer'],
			'email' => ['sometimes', 'nullable', 'email'],
			'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
		];
	}

	/**
	 * Process the validated values.
	 *
	 * @param array<string,mixed>        $values
	 * @param array<string,UploadedFile> $files
	 *
	 * @return void
	 *
	 * @throws ModelNotFoundException
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photo = Photo::query()->findOrFail($values['photo_id']);
		$this->album_id = $values['album_id'];
		$this->print_size = PrintSize::findOrFail($values['print_size_id']);
		$this->email = $values['email'] ?? null;
		$this->notes = $values['notes'] ?? null;
	}
}
