<?php

namespace App\DTO\Synth;

use App\Models\Photo;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

class PhotoSynth extends Synth
{
	public static $key = 'p';

	public static function match($target)
	{
		return $target instanceof Photo;
	}

	/**
	 * @param Photo $target
	 *
	 * @return string
	 */
	public function dehydrate($target)
	{
		return [[
			'id' => $target->id,
		], []];
	}

	/**
	 * @param array<string,string> $value
	 *
	 * @return Photo
	 */
	public function hydrate($value): Photo
	{
		return Photo::findOrFail($value['id']);
	}
}