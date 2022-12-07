<?php

namespace App\DTO;

class AlbumProtectionPolicy extends AbstractDTO
{
	public const IS_PUBLIC_ATTRIBUTE = 'is_public';
	public const REQUIRES_LINK_ATTRIBUTE = 'requires_link';
	public const IS_NSFW_ATTRIBUTE = 'is_nsfw';
	public const IS_DOWNLOADABLE_ATTRIBUTE = 'is_downloadable';
	public const IS_SHARE_BUTTON_VISIBLE_ATTRIBUTE = 'is_share_button_visible';
	public const GRANTS_FULL_PHOTO_ATTRIBUTE = 'grants_full_photo';

	public bool $isPublic;
	public bool $requiresLink;
	public bool $isNSFW;
	public bool $isDownloadable;
	public bool $isShareButtonVisible;
	public bool $grantsFullPhoto;

	public function __construct(
		bool $isPublic,
		bool $requiresLink,
		bool $isNSFW,
		bool $isDownloadable,
		bool $isShareButtonVisible,
		bool $grantsFullPhoto
	) {
		$this->isPublic = $isPublic;
		$this->requiresLink = $requiresLink;
		$this->isNSFW = $isNSFW;
		$this->isDownloadable = $isDownloadable;
		$this->isShareButtonVisible = $isShareButtonVisible;
		$this->grantsFullPhoto = $grantsFullPhoto;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			self::IS_PUBLIC_ATTRIBUTE => $this->isPublic,
			self::REQUIRES_LINK_ATTRIBUTE => $this->requiresLink,
			self::IS_NSFW_ATTRIBUTE => $this->isNSFW,
			self::IS_DOWNLOADABLE_ATTRIBUTE => $this->isDownloadable,
			self::IS_SHARE_BUTTON_VISIBLE_ATTRIBUTE => $this->isShareButtonVisible,
			self::GRANTS_FULL_PHOTO_ATTRIBUTE => $this->grantsFullPhoto,
		];
	}
}
