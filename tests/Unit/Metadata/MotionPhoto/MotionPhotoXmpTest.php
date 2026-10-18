<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\Metadata\MotionPhoto;

use App\Metadata\MotionPhoto\MotionPhotoXmp;
use Tests\AbstractTestCase;

/**
 * Covers SVC-100-02: reading the Google camera and container XMP of motion
 * photos (Feature 100, S-100-01, S-100-03, S-100-04, S-100-09, S-100-10).
 */
class MotionPhotoXmpTest extends AbstractTestCase
{
	/**
	 * The standard XMP packet of a Pixel 11 Pro Ultra HDR motion photo
	 * (FX-100-03), verbatim except for the maker note, which was stripped.
	 */
	private const PIXEL_XMP = <<<'XML'
		<x:xmpmeta xmlns:x="adobe:ns:meta/" x:xmptk="Adobe XMP Core 5.1.0-jc003">
		  <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
		    <rdf:Description rdf:about=""
		        xmlns:hdrgm="http://ns.adobe.com/hdr-gain-map/1.0/"
		        xmlns:GCamera="http://ns.google.com/photos/1.0/camera/"
		        xmlns:Container="http://ns.google.com/photos/1.0/container/"
		        xmlns:Item="http://ns.google.com/photos/1.0/container/item/"
		      hdrgm:Version="1.0"
		      GCamera:GFileMetadata="SgQIABAB"
		      GCamera:MotionPhoto="1"
		      GCamera:MotionPhotoVersion="1"
		      GCamera:MotionPhotoPresentationTimestampUs="1166896">
		      <Container:Directory>
		        <rdf:Seq>
		          <rdf:li rdf:parseType="Resource">
		            <Container:Item
		              Item:Mime="image/jpeg"
		              Item:Semantic="Primary"/>
		          </rdf:li>
		          <rdf:li rdf:parseType="Resource">
		            <Container:Item
		              Item:Length="1753"
		              Item:Mime="image/jpeg"
		              Item:Semantic="GainMap"/>
		          </rdf:li>
		          <rdf:li rdf:parseType="Resource">
		            <Container:Item
		              Item:Mime="video/mp4"
		              Item:Semantic="MotionPhoto"
		              Item:Length="3725212"
		              Item:Padding="0"/>
		          </rdf:li>
		        </rdf:Seq>
		      </Container:Directory>
		    </rdf:Description>
		  </rdf:RDF>
		</x:xmpmeta>
		XML;

	public function testPixelUltraHdrMotionPhoto(): void
	{
		$xmp = MotionPhotoXmp::fromXml(self::PIXEL_XMP);

		self::assertTrue($xmp->is_motion_photo);
		self::assertSame(3725212, $xmp->item_length);
		self::assertSame(0, $xmp->legacy_offset);
	}

	public function testMotionPhotoWithoutGainMap(): void
	{
		$xmp = MotionPhotoXmp::fromXml(self::motionPhoto(
			self::item('Primary') . self::item('MotionPhoto', 'Item:Length="10705" Item:Padding="0"')
		));

		self::assertTrue($xmp->is_motion_photo);
		self::assertSame(10705, $xmp->item_length);
	}

	public function testLengthBeforeSemantic(): void
	{
		$xmp = MotionPhotoXmp::fromXml(self::motionPhoto('<rdf:li rdf:parseType="Resource"><Container:Item Item:Length="42" Item:Semantic="MotionPhoto"/></rdf:li>'));

		self::assertSame(42, $xmp->item_length);
	}

	public function testMotionPhotoFlagWithoutVideoItem(): void
	{
		$xmp = MotionPhotoXmp::fromXml(self::motionPhoto(self::item('Primary')));

		self::assertTrue($xmp->is_motion_photo);
		self::assertSame(0, $xmp->item_length);
	}

	public function testMotionPhotoFlagWithoutDirectory(): void
	{
		$xmp = MotionPhotoXmp::fromXml(self::description('GCamera:MotionPhoto="1"', ''));

		self::assertTrue($xmp->is_motion_photo);
		self::assertSame(0, $xmp->item_length);
	}

	public function testVideoItemWithoutLength(): void
	{
		$xmp = MotionPhotoXmp::fromXml(self::motionPhoto(self::item('Primary') . self::item('MotionPhoto')));

		self::assertSame(0, $xmp->item_length);
	}

	public function testOnlyTheMotionPhotoItemLengthCounts(): void
	{
		$xmp = MotionPhotoXmp::fromXml(self::motionPhoto(self::item('GainMap', 'Item:Length="1753"')));

		self::assertSame(0, $xmp->item_length);
	}

	public function testLegacyMicroVideo(): void
	{
		$xmp = MotionPhotoXmp::fromXml(self::description('GCamera:MicroVideo="1" GCamera:MicroVideoVersion="1" GCamera:MicroVideoOffset="4495341" GCamera:MicroVideoPresentationTimestampUs="1366016"', ''));

		self::assertFalse($xmp->is_motion_photo);
		self::assertSame(0, $xmp->item_length);
		self::assertSame(4495341, $xmp->legacy_offset);
	}

	public function testElementSyntax(): void
	{
		$body = '<GCamera:MotionPhoto>1</GCamera:MotionPhoto>' .
			'<Container:Directory><rdf:Seq><rdf:li rdf:parseType="Resource"><Container:Item>' .
			'<Item:Semantic>MotionPhoto</Item:Semantic><Item:Length> 777 </Item:Length>' .
			'</Container:Item></rdf:li></rdf:Seq></Container:Directory>';
		$xmp = MotionPhotoXmp::fromXml(self::description('', $body));

		self::assertTrue($xmp->is_motion_photo);
		self::assertSame(777, $xmp->item_length);
	}

	public function testLegacyOffsetElementSyntax(): void
	{
		$xmp = MotionPhotoXmp::fromXml(self::description('', '<GCamera:MicroVideoOffset>1234</GCamera:MicroVideoOffset>'));

		self::assertSame(1234, $xmp->legacy_offset);
	}

	public function testNamespaceIsMatchedByUriNotPrefix(): void
	{
		$xml = '<x:xmpmeta xmlns:x="adobe:ns:meta/"><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">' .
			'<rdf:Description xmlns:Cam="http://ns.google.com/photos/1.0/camera/" xmlns:C="http://ns.google.com/photos/1.0/container/" xmlns:I="http://ns.google.com/photos/1.0/container/item/" Cam:MotionPhoto="1">' .
			'<C:Directory><rdf:Seq><rdf:li rdf:parseType="Resource"><C:Item I:Semantic="MotionPhoto" I:Length="99"/></rdf:li></rdf:Seq></C:Directory>' .
			'</rdf:Description></rdf:RDF></x:xmpmeta>';
		$xmp = MotionPhotoXmp::fromXml($xml);

		self::assertTrue($xmp->is_motion_photo);
		self::assertSame(99, $xmp->item_length);
	}

	public function testWrongNamespaceIsIgnored(): void
	{
		$xml = '<x:xmpmeta xmlns:x="adobe:ns:meta/"><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">' .
			'<rdf:Description xmlns:GCamera="http://example.com/not-google/" GCamera:MotionPhoto="1" GCamera:MicroVideoOffset="10"/>' .
			'</rdf:RDF></x:xmpmeta>';

		self::assertNone(MotionPhotoXmp::fromXml($xml));
	}

	public function testMotionPhotoFlagMustBeOne(): void
	{
		self::assertFalse(MotionPhotoXmp::fromXml(self::description('GCamera:MotionPhoto="0"', ''))->is_motion_photo);
	}

	public function testInvalidNumbersReadAsZero(): void
	{
		$xmp = MotionPhotoXmp::fromXml(self::description(
			'GCamera:MotionPhoto="1" GCamera:MicroVideoOffset="-5"',
			'<Container:Directory><rdf:Seq>' . self::item('MotionPhoto', 'Item:Length="12abc"') . '</rdf:Seq></Container:Directory>'
		));

		self::assertSame(0, $xmp->item_length);
		self::assertSame(0, $xmp->legacy_offset);
	}

	public function testUnrelatedXmp(): void
	{
		$xml = '<x:xmpmeta xmlns:x="adobe:ns:meta/"><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">' .
			'<rdf:Description xmlns:dc="http://purl.org/dc/elements/1.1/"><dc:subject><rdf:Bag><rdf:li>Mongolia</rdf:li></rdf:Bag></dc:subject></rdf:Description>' .
			'</rdf:RDF></x:xmpmeta>';

		self::assertNone(MotionPhotoXmp::fromXml($xml));
	}

	public function testMalformedXml(): void
	{
		self::assertNone(MotionPhotoXmp::fromXml(substr(self::PIXEL_XMP, 0, 900)));
	}

	public function testEmptyString(): void
	{
		self::assertNone(MotionPhotoXmp::fromXml(''));
	}

	public function testExternalEntitiesAreNotResolved(): void
	{
		$xml = '<?xml version="1.0"?><!DOCTYPE x [<!ENTITY ext SYSTEM "file:///etc/hosts">]>' .
			'<x:xmpmeta xmlns:x="adobe:ns:meta/"><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">' .
			'<rdf:Description xmlns:GCamera="http://ns.google.com/photos/1.0/camera/"><GCamera:MicroVideoOffset>&ext;</GCamera:MicroVideoOffset></rdf:Description>' .
			'</rdf:RDF></x:xmpmeta>';

		self::assertSame(0, MotionPhotoXmp::fromXml($xml)->legacy_offset);
	}

	private static function assertNone(MotionPhotoXmp $xmp): void
	{
		self::assertFalse($xmp->is_motion_photo);
		self::assertSame(0, $xmp->item_length);
		self::assertSame(0, $xmp->legacy_offset);
	}

	private static function item(string $semantic, string $extra = ''): string
	{
		return '<rdf:li rdf:parseType="Resource"><Container:Item Item:Mime="x/y" Item:Semantic="' . $semantic . '" ' . $extra . '/></rdf:li>';
	}

	private static function motionPhoto(string $items): string
	{
		return self::description('GCamera:MotionPhoto="1" GCamera:MotionPhotoVersion="1"', '<Container:Directory><rdf:Seq>' . $items . '</rdf:Seq></Container:Directory>');
	}

	private static function description(string $attributes, string $body): string
	{
		return '<x:xmpmeta xmlns:x="adobe:ns:meta/"><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">' .
			'<rdf:Description rdf:about=""' .
			' xmlns:GCamera="http://ns.google.com/photos/1.0/camera/"' .
			' xmlns:Container="http://ns.google.com/photos/1.0/container/"' .
			' xmlns:Item="http://ns.google.com/photos/1.0/container/item/" ' .
			$attributes . '>' . $body . '</rdf:Description></rdf:RDF></x:xmpmeta>';
	}
}
