<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\DTO;

use App\Enum\MessageType;

class DiagnosticData
{
	/**
	 * Create a Diagnostic Info.
	 *
	 * @param MessageType  $type
	 * @param string       $message
	 * @param class-string $from
	 * @param string[]     $details
	 */
	public function __construct(
		public MessageType $type,
		public string $message,
		public string $from,
		public array $details = [],
	) {
	}

	/**
	 * Quick static builder.
	 *
	 * @param string       $message
	 * @param class-string $from
	 * @param string[]     $details
	 *
	 * @return DiagnosticData
	 */
	public static function error(string $message, string $from, array $details = []): DiagnosticData
	{
		return new DiagnosticData(MessageType::ERROR, $message, $from, $details);
	}

	/**
	 * Quick static builder.
	 *
	 * @param string       $message
	 * @param class-string $from
	 * @param string[]     $details
	 *
	 * @return DiagnosticData
	 */
	public static function warn(string $message, string $from, array $details = []): DiagnosticData
	{
		return new DiagnosticData(MessageType::WARNING, $message, $from, $details);
	}

	/**
	 * Quick static builder.
	 *
	 * @param string       $message
	 * @param class-string $from
	 * @param string[]     $details
	 *
	 * @return DiagnosticData
	 */
	public static function info(string $message, string $from, array $details = []): DiagnosticData
	{
		return new DiagnosticData(MessageType::INFO, $message, $from, $details);
	}
}
