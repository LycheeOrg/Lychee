<?php

namespace App\Contracts\Livewire;

/**
 * This defines constants which are used as keys when sending dispatch messages between components.
 */
interface Params
{
	public const PARENT_ID = 'parentID';

	public const ALBUM_ID = 'albumID';
	public const ALBUM_IDS = 'albumIDs';

	public const PHOTO_ID = 'photoID';
	public const PHOTO_IDS = 'photoIDs';
}
