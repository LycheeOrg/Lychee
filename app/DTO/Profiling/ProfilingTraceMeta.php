<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO\Profiling;

/**
 * Metadata sidecar stored alongside each `.pprof` trace dump under
 * `storage/profiling` (DO-053-01).
 */
final class ProfilingTraceMeta
{
	public function __construct(
		public readonly ?string $route_name,
		public readonly string $method,
		public readonly string $path,
		public readonly int $status_code,
		public readonly float $duration_ms,
		public readonly int $peak_memory_bytes,
		public readonly ?int $user_id,
		public readonly string $created_at,
	) {
	}

	/**
	 * @return array{route_name:?string,method:string,path:string,status_code:int,duration_ms:float,peak_memory_bytes:int,user_id:?int,created_at:string}
	 */
	public function toJsonArray(): array
	{
		return [
			'route_name' => $this->route_name,
			'method' => $this->method,
			'path' => $this->path,
			'status_code' => $this->status_code,
			'duration_ms' => $this->duration_ms,
			'peak_memory_bytes' => $this->peak_memory_bytes,
			'user_id' => $this->user_id,
			'created_at' => $this->created_at,
		];
	}

	/**
	 * @param array{route_name?:?string,method?:string,path?:string,status_code?:int,duration_ms?:float,peak_memory_bytes?:int,user_id?:?int,created_at?:string} $data
	 */
	public static function fromJsonArray(array $data): self
	{
		return new self(
			route_name: $data['route_name'] ?? null,
			method: $data['method'] ?? '',
			path: $data['path'] ?? '',
			status_code: $data['status_code'] ?? 0,
			duration_ms: $data['duration_ms'] ?? 0.0,
			peak_memory_bytes: $data['peak_memory_bytes'] ?? 0,
			user_id: $data['user_id'] ?? null,
			created_at: $data['created_at'] ?? '',
		);
	}
}
