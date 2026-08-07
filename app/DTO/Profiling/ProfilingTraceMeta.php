<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO\Profiling;

/**
 * Metadata sidecar stored under `storage/profiling` for each profiled
 * request (DO-053-01). `spx_report_key` correlates this sidecar with the
 * `spx` extension's own report files (`{spx_report_key}.json` /
 * `{spx_report_key}.txt.gz`, written directly by the extension into the
 * same directory), so the admin page can show meaningful per-request
 * context (route, method, status) while linking out to SPX's own analysis
 * screen for the actual call-graph.
 */
final class ProfilingTraceMeta
{
	public function __construct(
		public readonly ?string $spx_report_key,
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
	 * @return array{spx_report_key:?string,route_name:?string,method:string,path:string,status_code:int,duration_ms:float,peak_memory_bytes:int,user_id:?int,created_at:string}
	 */
	public function toJsonArray(): array
	{
		return [
			'spx_report_key' => $this->spx_report_key,
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
	 * @param array{spx_report_key?:?string,route_name?:?string,method?:string,path?:string,status_code?:int,duration_ms?:float,peak_memory_bytes?:int,user_id?:?int,created_at?:string} $data
	 */
	public static function fromJsonArray(array $data): self
	{
		return new self(
			spx_report_key: $data['spx_report_key'] ?? null,
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
