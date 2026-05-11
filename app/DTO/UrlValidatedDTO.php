<?php

namespace App\DTO;

final class UrlValidatedDTO
{
	public function __construct(
		public string $url,
		public ?string $resolved_ip,
		public ?string $error,
	) {
	}

	public static function fromError(string $url, string $error): self
	{
		return new self(
			url: $url,
			resolved_ip: null,
			error: $error,
		);
	}
}