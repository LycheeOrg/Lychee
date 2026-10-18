<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Metadata\MotionPhoto;

use function Safe\xml_parser_set_option;

/**
 * What the XMP packet of a JPEG says about an embedded motion photo video
 * (Feature 100, DO-100-02).
 *
 * Both lengths count bytes from the end of the file; `0` means "not
 * declared". Nothing here is validated against the file: that is the job of
 * {@see MotionPhotoLocator}.
 */
final readonly class MotionPhotoXmp
{
	public const NS_CAMERA = 'http://ns.google.com/photos/1.0/camera/';
	public const NS_CONTAINER = 'http://ns.google.com/photos/1.0/container/';
	public const NS_ITEM = 'http://ns.google.com/photos/1.0/container/item/';

	/**
	 * @param bool $is_motion_photo `GCamera:MotionPhoto` is `1` (Motion Photo 1.0)
	 * @param int  $item_length     `Item:Length` of the container item with semantic `MotionPhoto`
	 * @param int  $legacy_offset   `GCamera:MicroVideoOffset` (Android ≤ 10)
	 */
	public function __construct(
		public bool $is_motion_photo,
		public int $item_length,
		public int $legacy_offset,
	) {
	}

	public static function none(): self
	{
		return new self(false, 0, 0);
	}

	/**
	 * Parses an XMP packet; malformed or unrelated XML yields {@see none()}.
	 *
	 * The SAX parser of `ext-xml` does not load external entities, so the
	 * untrusted packet cannot pull in other files.
	 */
	public static function fromXml(string $xml): self
	{
		$handler = new MotionPhotoXmpHandler();
		$parser = xml_parser_create_ns('UTF-8', MotionPhotoXmpHandler::SEPARATOR);
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_set_element_handler($parser, $handler->start(...), $handler->end(...));
		xml_set_character_data_handler($parser, $handler->text(...));

		$is_well_formed = xml_parse($parser, $xml, true) === 1;

		return $is_well_formed ? $handler->result() : self::none();
	}
}
