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

namespace Tests\Unit\Services;

use App\Services\GpxValidation;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AbstractTestCase;
use Tests\Constants\TestConstants;

/**
 * Unit tests for {@link GpxValidation}.
 */
class GpxValidationTest extends AbstractTestCase
{
	private const HEADER = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	private const GPX_OPEN = '<gpx version="1.1" creator="test" xmlns="http://www.topografix.com/GPX/1/1">';
	private const GPX_CLOSE = '</gpx>';

	private static function gpx(string $body): string
	{
		return self::HEADER . self::GPX_OPEN . $body . self::GPX_CLOSE;
	}

	private static function uploadedFile(string $content, string $name = 'track.gpx'): UploadedFile
	{
		$tmp = tempnam(sys_get_temp_dir(), 'lychee');
		file_put_contents($tmp, $content);

		return new UploadedFile($tmp, $name, 'application/gpx+xml', UPLOAD_ERR_OK, true);
	}

	public function testSampleFixturesAreValid(): void
	{
		$validation = new GpxValidation();
		self::assertNull($validation->validate(static::createUploadedFile(TestConstants::SAMPLE_FILE_GPX)));
		self::assertNull($validation->validate(static::createUploadedFile(TestConstants::SAMPLE_FILE_GPX2)));
	}

	public function testMinimalDocumentWithoutXmlDeclarationIsValid(): void
	{
		$content = self::GPX_OPEN . '<trk><trkseg><trkpt lat="1" lon="2"/></trkseg></trk>' . self::GPX_CLOSE;
		self::assertNull((new GpxValidation())->validate(self::uploadedFile($content)));
	}

	public function testRejectsEmptyFile(): void
	{
		self::assertSame('is empty.', (new GpxValidation())->validate(self::uploadedFile('')));
	}

	public function testRejectsOversizedFile(): void
	{
		$validation = new GpxValidation(max_file_size: 100);
		$content = self::gpx(str_repeat('<wpt lat="1" lon="1"/>', 10));

		self::assertSame('exceeds the maximum allowed size.', $validation->validate(self::uploadedFile($content)));
		self::assertSame('exceeds the maximum allowed size.', $validation->validateContent($content));
	}

	public function testRejectsWrongExtension(): void
	{
		self::assertSame('must have a .gpx extension.', (new GpxValidation())->validate(self::uploadedFile(self::gpx(''), 'track.xml')));
	}

	public function testRejectsWrongMimeType(): void
	{
		$png = file_get_contents(base_path(TestConstants::SAMPLE_FILE_PNG));
		self::assertSame('has an invalid MIME type.', (new GpxValidation())->validate(self::uploadedFile($png)));
	}

	/**
	 * @return array<string,array{string,string}>
	 */
	public static function invalidContentProvider(): array
	{
		return [
			'XXE external entity' => [
				self::HEADER . '<!DOCTYPE gpx [<!ENTITY xxe SYSTEM "file:///etc/passwd">]>' . self::GPX_OPEN . '<name>&xxe;</name>' . self::GPX_CLOSE,
				'must not contain a DOCTYPE declaration.',
			],
			'billion laughs' => [
				self::HEADER . '<!DOCTYPE lolz [<!ENTITY lol "lol"><!ENTITY lol2 "&lol;&lol;&lol;">]>' . self::GPX_OPEN . '<name>&lol2;</name>' . self::GPX_CLOSE,
				'must not contain a DOCTYPE declaration.',
			],
			'UTF-16 declaration' => [
				'<?xml version="1.0" encoding="UTF-16"?>' . self::GPX_OPEN . self::GPX_CLOSE,
				'must be UTF-8 encoded.',
			],
			'invalid UTF-8' => [
				self::gpx("<name>\xC3\x28</name>"),
				'must be UTF-8 encoded.',
			],
			'stylesheet processing instruction' => [
				self::HEADER . '<?xml-stylesheet type="text/xsl" href="evil.xsl"?>' . self::GPX_OPEN . self::GPX_CLOSE,
				'must not contain stylesheet processing instructions.',
			],
			'script element' => [
				self::gpx('<script>alert(1)</script>'),
				'must not contain HTML or script elements.',
			],
			'script element in CDATA' => [
				self::gpx('<wpt lat="1" lon="1"><desc><![CDATA[<script>alert(1)</script>]]></desc></wpt>'),
				'must not contain HTML or script elements.',
			],
			'svg element' => [
				self::gpx('<extensions><svg onload="alert(1)"/></extensions>'),
				'must not contain HTML or script elements.',
			],
			'event handler' => [
				self::gpx('<wpt lat="1" lon="1" onclick="alert(1)"/>'),
				'must not contain event handler attributes.',
			],
			'javascript protocol' => [
				self::gpx('<metadata><link href="javascript:alert(1)"/></metadata>'),
				'must not contain script protocols.',
			],
			'data URI' => [
				self::gpx('<metadata><link href="data:text/html;base64,PHNjcmlwdD4="/></metadata>'),
				'must not contain data URIs.',
			],
			'xhtml namespace' => [
				self::gpx('<extensions><h:p xmlns:h="http://www.w3.org/1999/xhtml">x</h:p></extensions>'),
				'must not reference HTML, SVG, XLink or XInclude namespaces.',
			],
			'entity-encoded javascript protocol' => [
				self::gpx('<metadata><link href="java&#x73;cript&#x3A;alert(1)"/></metadata>'),
				'contains a link with a forbidden protocol.',
			],
			'file protocol' => [
				self::gpx('<metadata><link href="file:///etc/passwd"/></metadata>'),
				'contains a link with a forbidden protocol.',
			],
			'malformed XML' => [
				self::gpx('<trk><trkseg></trk>'),
				'is not well-formed XML.',
			],
			'not XML' => [
				'just some text',
				'is not well-formed XML.',
			],
			'wrong root element' => [
				self::HEADER . '<kml xmlns="http://www.opengis.net/kml/2.2"></kml>',
				'must have a <gpx> root element.',
			],
			'missing namespace' => [
				self::HEADER . '<gpx version="1.1" creator="test"></gpx>',
				'must use the GPX 1.0 or 1.1 namespace.',
			],
			'version mismatch' => [
				self::HEADER . '<gpx version="1.0" creator="test" xmlns="http://www.topografix.com/GPX/1/1"></gpx>',
				'has an invalid GPX version.',
			],
			'missing coordinates' => [
				self::gpx('<wpt lat="1"/>'),
				'contains a point with invalid coordinates.',
			],
			'out of range coordinates' => [
				self::gpx('<trk><trkseg><trkpt lat="91" lon="0"/></trkseg></trk>'),
				'contains a point with invalid coordinates.',
			],
			'unknown GPX element' => [
				self::gpx('<foo/>'),
				'contains a non-GPX element <foo>.',
			],
			'foreign element outside extensions (1.1)' => [
				self::gpx('<x:foo xmlns:x="urn:example"/>'),
				'contains a non-GPX element <x:foo>.',
			],
			'unknown GPX attribute' => [
				self::gpx('<wpt lat="1" lon="1" style="color:red"/>'),
				'contains a non-GPX attribute "style".',
			],
			'processing instruction' => [
				self::gpx('<?php echo 1; ?>'),
				'contains a forbidden node type.',
			],
			'too deep' => [
				self::gpx('<extensions>' . str_repeat('<x:a xmlns:x="urn:example">', 40) . str_repeat('</x:a>', 40) . '</extensions>'),
				'is nested too deeply.',
			],
		];
	}

	#[DataProvider('invalidContentProvider')]
	public function testRejectsInvalidContent(string $content, string $expected_error): void
	{
		self::assertSame($expected_error, (new GpxValidation())->validateContent($content));
	}

	public function testAllowsForeignElementsInsideExtensions(): void
	{
		$content = self::gpx('<wpt lat="1" lon="1"><extensions><x:foo xmlns:x="urn:example" bar="1">baz</x:foo></extensions></wpt>');
		self::assertNull((new GpxValidation())->validateContent($content));
	}

	public function testAllowsPrivateElementsInGpx10(): void
	{
		$content = self::HEADER . '<gpx version="1.0" creator="test" xmlns="http://www.topografix.com/GPX/1/0" xmlns:x="urn:example"><x:foo/></gpx>';
		self::assertNull((new GpxValidation())->validateContent($content));
	}

	public function testRestoresDefaultEntityLoader(): void
	{
		self::assertNull(libxml_get_external_entity_loader());
		(new GpxValidation())->validateContent(self::gpx(''));
		self::assertNull(libxml_get_external_entity_loader());
	}

	public function testRestoresCustomEntityLoader(): void
	{
		$loader = static fn (): null => null;
		libxml_set_external_entity_loader($loader);

		try {
			(new GpxValidation())->validateContent(self::gpx(''));
			self::assertSame($loader, libxml_get_external_entity_loader());
		} finally {
			libxml_set_external_entity_loader(null);
		}
	}

	public function testAllowsRelativeAndHttpLinks(): void
	{
		$content = self::gpx('<metadata><link href="photos/1.jpg"/><link href="http://example.com"/></metadata>');
		self::assertNull((new GpxValidation())->validateContent($content));
	}
}
