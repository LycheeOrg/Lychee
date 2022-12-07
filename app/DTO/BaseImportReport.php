<?php

namespace App\DTO;

/**
 * Class ImportReport.
 *
 * This DTO is used for messaging from the server to the client within a
 * streamed JSON response to avoid timeouts due to long-running requests.
 * There are several types of reports implemented as subclasses, such as
 * informational, cumulative progress reports or events.
 *
 * Note, that the HTTP result code is sent as part of the headers at the
 * beginning of a streamed response.
 * Hence, errors cannot be reported using the normal exception handling
 * mechanism, but must be reported "inline" within the streamed response
 * as an event report.
 */
abstract class BaseImportReport extends AbstractDTO
{
	/**
	 * Indicates the type (i.e. the subclass) of this class.
	 * This information is required by the front-end to correctly cast
	 * the response into the correct type.
	 *
	 * @var string
	 */
	protected string $type;

	protected function __construct(string $type)
	{
		$this->type = $type;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'type' => $this->type,
		];
	}

	abstract public function toCLIString(): string;
}
