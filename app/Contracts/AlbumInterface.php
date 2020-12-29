<?php

namespace App\Contracts;

interface AlbumInterface
{
	public function get_photos();

	public function owner();
}
