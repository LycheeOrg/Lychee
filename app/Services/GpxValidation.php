<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Safe\Exceptions\UrlException;
use function Safe\file_get_contents;
use function Safe\libxml_set_external_entity_loader;
use function Safe\parse_url;
use function Safe\preg_match;

/**
 * Validates that an uploaded file is a genuine, harmless GPX document.
 *
 * Checks are applied cheapest first and the first failure is reported:
 * 1. size limits (DoS),
 * 2. file extension and detected MIME type,
 * 3. raw scan for dangerous patterns (DTD/entities, scripts, event handlers, protocols, non UTF-8 encodings),
 * 4. XML parsing with network access and entity substitution disabled (XXE),
 * 5. GPX structure (root element, namespace, version, coordinates),
 * 6. element and attribute whitelist.
 */
class GpxValidation
{
	public const MAX_FILE_SIZE = 20 * 1024 * 1024;
	public const MAX_DEPTH = 32;

	public const NAMESPACE_GPX_1_0 = 'http://www.topografix.com/GPX/1/0';
	public const NAMESPACE_GPX_1_1 = 'http://www.topografix.com/GPX/1/1';
	private const NAMESPACE_XSI = 'http://www.w3.org/2001/XMLSchema-instance';

	private const ALLOWED_EXTENSIONS = ['gpx'];
	private const ALLOWED_MIME_TYPES = ['application/gpx+xml', 'application/xml', 'text/xml', 'text/plain'];

	/** Namespace => version attribute expected on the root element. */
	private const GPX_VERSIONS = [
		self::NAMESPACE_GPX_1_0 => '1.0',
		self::NAMESPACE_GPX_1_1 => '1.1',
	];

	/**
	 * Patterns which never occur in a legitimate GPX file.
	 * Matched case-insensitively against the raw content.
	 */
	private const DANGEROUS_PATTERNS = [
		'/<!DOCTYPE/i' => 'must not contain a DOCTYPE declaration.',
		'/<!ENTITY/i' => 'must not contain entity declarations.',
		'/<\?xml-stylesheet/i' => 'must not contain stylesheet processing instructions.',
		'/<\s*(script|iframe|object|embed|svg|html|body|img|style|link\s+rel)\b/i' => 'must not contain HTML or script elements.',
		'/<[^>]*\son[a-z]+\s*=/i' => 'must not contain event handler attributes.',
		'/(javascript|vbscript|livescript)\s*:/i' => 'must not contain script protocols.',
		'/\bdata\s*:[^,\s]*\/[^,\s]*[;,]/i' => 'must not contain data URIs.',
		'/xmlns(:\w+)?\s*=\s*["\']http:\/\/www\.w3\.org\/(1999\/xhtml|2000\/svg|1999\/xlink|2001\/XInclude)["\']/i' => 'must not reference HTML, SVG, XLink or XInclude namespaces.',
	];

	/** Elements of the GPX 1.0 and 1.1 schemas. */
	private const ALLOWED_ELEMENTS = [
		'gpx', 'metadata', 'name', 'desc', 'author', 'email', 'link', 'text', 'type',
		'copyright', 'year', 'license', 'time', 'keywords', 'bounds', 'extensions',
		'wpt', 'ele', 'magvar', 'geoidheight', 'cmt', 'src', 'sym', 'fix', 'sat',
		'hdop', 'vdop', 'pdop', 'ageofdgpsdata', 'dgpsid', 'rte', 'number', 'rtept',
		'trk', 'trkseg', 'trkpt',
		// GPX 1.0 only.
		'url', 'urlname', 'course', 'speed',
	];

	/** Attributes of the GPX 1.0 and 1.1 schemas. */
	private const ALLOWED_ATTRIBUTES = [
		'version', 'creator', 'lat', 'lon', 'href', 'id', 'domain', 'author',
		'minlat', 'minlon', 'maxlat', 'maxlon',
	];

	/** Elements carrying mandatory lat/lon attributes. */
	private const POINT_ELEMENTS = ['wpt', 'rtept', 'trkpt'];

	/** Namespaces never allowed, even inside extensions. */
	private const FORBIDDEN_NAMESPACES = [
		'http://www.w3.org/1999/xhtml',
		'http://www.w3.org/2000/svg',
		'http://www.w3.org/1999/xlink',
		'http://www.w3.org/2001/XInclude',
	];

	private const ALLOWED_URL_SCHEMES = ['http', 'https'];

	/**
	 * @param int $max_file_size maximum accepted file size in bytes
	 */
	public function __construct(
		private int $max_file_size = self::MAX_FILE_SIZE,
	) {
	}

	/**
	 * Validate the uploaded file.
	 *
	 * @return string|null the error message, null if the file is a valid GPX file
	 */
	public function validate(UploadedFile $file): ?string
	{
		$error = $this->checkSize($file)
			?? $this->checkExtension($file)
			?? $this->checkMimeType($file);
		if ($error !== null) {
			return $error;
		}

		$content = file_get_contents($file->getRealPath());

		return $this->validateContent($content);
	}

	/**
	 * Validate raw GPX content.
	 *
	 * @return string|null the error message, null if the content is a valid GPX document
	 */
	public function validateContent(string $content): ?string
	{
		if (strlen($content) > $this->max_file_size) {
			return 'exceeds the maximum allowed size.';
		}

		$error = $this->checkEncoding($content) ?? $this->scanDangerousPatterns($content);
		if ($error !== null) {
			return $error;
		}

		$document = $this->parse($content);
		if ($document === null) {
			return 'is not well-formed XML.';
		}

		return $this->checkStructure($document) ?? $this->checkWhitelist($document);
	}

	private function checkSize(UploadedFile $file): ?string
	{
		$size = $file->getSize();
		if ($size === false || $size === 0) {
			return 'is empty.';
		}

		return $size > $this->max_file_size ? 'exceeds the maximum allowed size.' : null;
	}

	private function checkExtension(UploadedFile $file): ?string
	{
		$extension = strtolower($file->getClientOriginalExtension());

		return in_array($extension, self::ALLOWED_EXTENSIONS, true) ? null : 'must have a .gpx extension.';
	}

	private function checkMimeType(UploadedFile $file): ?string
	{
		// Detected from the file content (finfo), not the client-provided type.
		$mime_type = $file->getMimeType();

		return in_array($mime_type, self::ALLOWED_MIME_TYPES, true) ? null : 'has an invalid MIME type.';
	}

	/**
	 * Only UTF-8 is accepted: other encodings (e.g. UTF-16) would let
	 * dangerous patterns slip through the raw scan.
	 */
	private function checkEncoding(string $content): ?string
	{
		if (!mb_check_encoding($content, 'UTF-8')) {
			return 'must be UTF-8 encoded.';
		}

		if (preg_match('/^(\xEF\xBB\xBF)?\s*<\?xml[^>]*\bencoding\s*=\s*["\'](?!utf-8["\'])/i', $content) === 1) {
			return 'must be UTF-8 encoded.';
		}

		return null;
	}

	private function scanDangerousPatterns(string $content): ?string
	{
		foreach (self::DANGEROUS_PATTERNS as $pattern => $error) {
			if (preg_match($pattern, $content) === 1) {
				return $error;
			}
		}

		return null;
	}

	/**
	 * Parse without network access, without loading or validating against a DTD
	 * and without substituting entities (no LIBXML_NOENT / LIBXML_DTDLOAD),
	 * so that external entities are never resolved. On top of that, the external
	 * entity loader is replaced for the duration of the parse by one refusing
	 * every resource (XXE), and the previous loader is restored afterwards.
	 * DOCTYPEs are already rejected by the raw scan; the doctype check below is
	 * defence in depth.
	 */
	private function parse(string $content): ?\DOMDocument
	{
		$previous_use_errors = libxml_use_internal_errors(true);
		$previous_entity_loader = libxml_get_external_entity_loader();
		libxml_set_external_entity_loader(static fn (): null => null);

		try {
			$document = new \DOMDocument();
			$loaded = $document->loadXML($content, LIBXML_NONET | LIBXML_NOCDATA);
		} finally {
			$this->restoreEntityLoader($previous_entity_loader);
			libxml_clear_errors();
			libxml_use_internal_errors($previous_use_errors);
		}

		return $loaded === true && $document->doctype === null ? $document : null;
	}

	/**
	 * Restore the external entity loader active before parsing.
	 * `null` (libxml's default loader) can only be restored via the native function:
	 * the Safe wrapper only accepts a callable.
	 */
	private function restoreEntityLoader(?callable $previous_entity_loader): void
	{
		if ($previous_entity_loader !== null) {
			libxml_set_external_entity_loader($previous_entity_loader);

			return;
		}

		// @phpstan-ignore theCodingMachineSafe.function
		\libxml_set_external_entity_loader(null);
	}

	private function checkStructure(\DOMDocument $document): ?string
	{
		$root = $document->documentElement;
		if ($root === null || $root->localName !== 'gpx') {
			return 'must have a <gpx> root element.';
		}

		$namespace = $root->namespaceURI ?? '';
		if (!array_key_exists($namespace, self::GPX_VERSIONS)) {
			return 'must use the GPX 1.0 or 1.1 namespace.';
		}

		if ($root->getAttribute('version') !== self::GPX_VERSIONS[$namespace]) {
			return 'has an invalid GPX version.';
		}

		foreach (self::POINT_ELEMENTS as $point_name) {
			foreach ($document->getElementsByTagNameNS($namespace, $point_name) as $point) {
				if (!$this->hasValidCoordinates($point)) {
					return 'contains a point with invalid coordinates.';
				}
			}
		}

		return null;
	}

	private function hasValidCoordinates(\DOMElement $point): bool
	{
		$lat = $point->getAttribute('lat');
		$lon = $point->getAttribute('lon');

		return is_numeric($lat) && is_numeric($lon) &&
			abs((float) $lat) <= 90 && abs((float) $lon) <= 180;
	}

	/**
	 * Walk the whole tree: only whitelisted GPX elements/attributes are allowed.
	 * Foreign-namespace elements are only tolerated inside <extensions> (GPX 1.1)
	 * or as private elements (GPX 1.0), and never in HTML/SVG/XLink/XInclude namespaces.
	 */
	private function checkWhitelist(\DOMDocument $document): ?string
	{
		/** @var \DOMElement $root */
		$root = $document->documentElement;
		$gpx_namespace = $root->namespaceURI;
		$is_gpx_1_1 = $gpx_namespace === self::NAMESPACE_GPX_1_1;

		/** @var array<int,array{\DOMNode,int,bool}> $stack node, depth, inside extensions */
		$stack = [[$root, 1, false]];
		while ($stack !== []) {
			[$node, $depth, $in_extensions] = array_pop($stack);

			if ($depth > self::MAX_DEPTH) {
				return 'is nested too deeply.';
			}

			$error = $this->checkNode($node, $gpx_namespace, $is_gpx_1_1, $in_extensions);
			if ($error !== null) {
				return $error;
			}

			$children_in_extensions = $in_extensions || ($node->namespaceURI === $gpx_namespace && $node->localName === 'extensions');
			foreach ($node->childNodes as $child) {
				$stack[] = [$child, $depth + 1, $children_in_extensions];
			}
		}

		return null;
	}

	private function checkNode(\DOMNode $node, ?string $gpx_namespace, bool $is_gpx_1_1, bool $in_extensions): ?string
	{
		return match (true) {
			$node instanceof \DOMElement => $this->checkElement($node, $gpx_namespace, $is_gpx_1_1, $in_extensions),
			$node instanceof \DOMText, $node instanceof \DOMComment => null,
			default => 'contains a forbidden node type.',
		};
	}

	private function checkElement(\DOMElement $element, ?string $gpx_namespace, bool $is_gpx_1_1, bool $in_extensions): ?string
	{
		$namespace = $element->namespaceURI;
		if (in_array($namespace, self::FORBIDDEN_NAMESPACES, true)) {
			return 'contains an element from a forbidden namespace.';
		}

		if ($namespace !== $gpx_namespace) {
			// Foreign elements: GPX 1.1 only allows them within <extensions>.
			$allowed = $namespace !== null && (!$is_gpx_1_1 || $in_extensions);

			return $allowed ? $this->checkAttributes($element, $gpx_namespace, false) : 'contains a non-GPX element <' . $element->nodeName . '>.';
		}

		if (!in_array($element->localName, self::ALLOWED_ELEMENTS, true)) {
			return 'contains a non-GPX element <' . $element->nodeName . '>.';
		}

		return $this->checkAttributes($element, $gpx_namespace, true);
	}

	private function checkAttributes(\DOMElement $element, ?string $gpx_namespace, bool $is_gpx_element): ?string
	{
		/** @var \DOMAttr $attribute */
		foreach ($element->attributes ?? [] as $attribute) {
			$error = $this->checkAttribute($attribute, $gpx_namespace, $is_gpx_element);
			if ($error !== null) {
				return $error;
			}
		}

		return null;
	}

	private function checkAttribute(\DOMAttr $attribute, ?string $gpx_namespace, bool $is_gpx_element): ?string
	{
		$name = strtolower($attribute->localName ?? '');
		$namespace = $attribute->namespaceURI;

		if (str_starts_with($name, 'on') || in_array($namespace, self::FORBIDDEN_NAMESPACES, true)) {
			return 'contains a forbidden attribute "' . $attribute->nodeName . '".';
		}

		if ($name === 'href' && !$this->isSafeUrl($attribute->value)) {
			return 'contains a link with a forbidden protocol.';
		}

		// Unprefixed attributes have no namespace; xsi:schemaLocation and the like are tolerated.
		$is_allowed = !$is_gpx_element ||
			$namespace === self::NAMESPACE_XSI ||
			(($namespace === null || $namespace === $gpx_namespace) && in_array($name, self::ALLOWED_ATTRIBUTES, true));

		return $is_allowed ? null : 'contains a non-GPX attribute "' . $attribute->nodeName . '".';
	}

	private function isSafeUrl(string $url): bool
	{
		try {
			$scheme = parse_url(trim($url), PHP_URL_SCHEME);
		} catch (UrlException) {
			return false;
		}

		// Relative links have no scheme.
		return $scheme === null || in_array(strtolower((string) $scheme), self::ALLOWED_URL_SCHEMES, true);
	}
}
