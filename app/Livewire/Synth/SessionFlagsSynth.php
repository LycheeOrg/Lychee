<?php

namespace App\Livewire\Synth;

use App\Livewire\DTO\SessionFlags;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

class SessionFlagsSynth extends Synth
{
	public static string $key = 's';

	public static function match(mixed $target): bool
	{
		return $target instanceof SessionFlags;
	}

	/**
	 * @param SessionFlags $target
	 *
	 * @return array<int,array<string,bool>>
	 */
	public function dehydrate($target): array
	{
		return [[
			'can_fullscreen' => $target->can_fullscreen,
			'is_fullscreen' => $target->is_fullscreen,
			'are_photo_details_open' => $target->are_photo_details_open,
		], []];
	}

	/**
	 * @param array<string,bool> $value
	 *
	 * @return SessionFlags
	 */
	public function hydrate($value): SessionFlags
	{
		return new SessionFlags(
			can_fullscreen: $value['can_fullscreen'],
			is_fullscreen: $value['is_fullscreen'],
			are_photo_details_open: $value['are_photo_details_open'],
		);
	}

	/**
	 * @param SessionFlags $target
	 * @param string       $key
	 *
	 * @return string
	 */
	public function get(&$target, $key)
	{
		return $target->{$key};
	}

	/**
	 * @param SessionFlags $target
	 * @param string       $key
	 * @param bool  $value
	 *
	 * @return void
	 */
	public function set(&$target, $key, $value)
	{
		$target->{$key} = $value;
		$target->save();
	}
}