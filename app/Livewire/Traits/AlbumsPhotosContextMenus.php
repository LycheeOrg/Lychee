<?php

namespace App\Livewire\Traits;

use App\Contracts\Livewire\Params;
use App\Livewire\Components\Base\ContextMenu;
use Livewire\Attributes\Renderless;

/**
 * @property string|null $albumId
 */
trait AlbumsPhotosContextMenus
{
	use InteractWithContextMenu;

	#[Renderless]
	public function openContextMenu(): void
	{
		$this->dispatch(
			'openContextMenu',
			'menus.AlbumAdd',
			[Params::PARENT_ID => $this->albumId],
			'right: 30px; top: 30px; transform-origin: top right;'
		)->to(ContextMenu::class);
	}

	#[Renderless]
	public function openPhotoDropdown(int $x, int $y, string $photoId): void
	{
		$this->dispatch(
			'openContextMenu',
			'menus.PhotoDropdown',
			[Params::ALBUM_ID => $this->albumId, Params::PHOTO_ID => $photoId],
			$this->getCss($x, $y)
		)->to(ContextMenu::class);
	}

	#[Renderless]
	public function openPhotosDropdown(int $x, int $y, array $photoIds): void
	{
		$this->dispatch(
			'openContextMenu',
			'menus.PhotosDropdown',
			[Params::ALBUM_ID => $this->albumId, Params::PHOTO_IDS => $photoIds],
			$this->getCss($x, $y)
		)->to(ContextMenu::class);
	}

	#[Renderless]
	public function openAlbumDropdown(int $x, int $y, string $albumID): void
	{
		$this->dispatch(
			'openContextMenu',
			'menus.AlbumDropdown',
			[Params::PARENT_ID => $this->albumId, Params::ALBUM_ID => $albumID],
			$this->getCss($x, $y)
		)->to(ContextMenu::class);
	}

	#[Renderless]
	public function openAlbumsDropdown(int $x, int $y, array $albumIds): void
	{
		$this->dispatch(
			'openContextMenu',
			'menus.AlbumsDropdown',
			[Params::PARENT_ID => $this->albumId, Params::ALBUM_IDS => $albumIds],
			$this->getCss($x, $y)
		)->to(ContextMenu::class);
	}

	private function getCss(int $x, int $y): string
	{
		return sprintf('transform-origin: top left; left: %dpx; top: %dpx;', $x, $y);
	}
}
