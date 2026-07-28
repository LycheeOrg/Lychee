<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex">

		<title>Memory Profiler</title>

		<style>
			html, body {
				background-color: #fff;
				color: #2c2c2c;
				font-family: sans-serif;
				margin: 0;
				padding: 24px;
			}

			h1 {
				font-size: 22px;
				margin: 0 0 16px 0;
			}

			.warning {
				background-color: #fff3cd;
				border: 1px solid #ffe69c;
				color: #664d03;
				padding: 10px 14px;
				border-radius: 4px;
				margin-bottom: 16px;
			}

			.empty-state {
				background-color: #f4f4f4;
				border: 1px solid #ddd;
				padding: 16px;
				border-radius: 4px;
			}

			table {
				border-collapse: collapse;
				width: 100%;
			}

			th, td {
				text-align: left;
				padding: 8px 12px;
				border-bottom: 1px solid #ddd;
				font-size: 14px;
			}

			th {
				background-color: #f4f4f4;
			}

			a.view-link {
				color: #0b5ed7;
				text-decoration: none;
			}

			a.view-link:hover {
				text-decoration: underline;
			}

			form.prune-form {
				margin-bottom: 16px;
			}
		</style>
	</head>
	<body>
		<h1>Memory Profiler</h1>

		@if ($is_octane)
			<div class="warning">
				⚠ This server is running under Laravel Octane/FrankenPHP. Capture uses SPX's manual
				start/stop spans specifically to remain correct under this runtime — see ADR-0008 for details.
			</div>
		@endif

		@if (!$spx_key_configured)
			<div class="warning">
				⚠ <code>MEMORY_PROFILER_SPX_KEY</code> is not set. Traces will still be captured, but the
				"view" link below cannot be built without it — see <code>docs/specs/2-how-to/enable-memory-profiler.md</code>.
			</div>
		@endif

		<form class="prune-form" method="POST" action="{{ route('admin.profiler.prune') }}">
			@csrf
			<button type="submit">Prune old traces</button>
		</form>

		@if ($traces->isEmpty())
			<div class="empty-state">
				No traces collected yet. Make sure <code>MEMORY_PROFILER_ENABLED=true</code> and the
				<code>spx</code> extension is loaded — see
				<code>docs/specs/2-how-to/enable-memory-profiler.md</code>.
			</div>
		@else
			<table>
				<thead>
					<tr>
						<th>Captured at</th>
						<th>Route</th>
						<th>Method</th>
						<th>Path</th>
						<th>Status</th>
						<th>Duration</th>
						<th>Peak mem</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					@foreach ($traces as $trace)
						<tr>
							<td>{{ $trace['meta']->created_at }}</td>
							<td>{{ $trace['meta']->route_name ?? '—' }}</td>
							<td>{{ $trace['meta']->method }}</td>
							<td>{{ $trace['meta']->path }}</td>
							<td>{{ $trace['meta']->status_code }}</td>
							<td>{{ number_format($trace['meta']->duration_ms, 1) }} ms</td>
							<td>{{ \Illuminate\Support\Number::fileSize($trace['meta']->peak_memory_bytes, 1) }}</td>
							<td>
								@if ($trace['spx_url'] !== null)
									<a class="view-link" href="{{ $trace['spx_url'] }}" target="_blank" rel="noopener">open in SPX &rarr;</a>
								@else
									<span title="No SPX report key captured for this trace, or MEMORY_PROFILER_SPX_KEY is not set">—</span>
								@endif
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		@endif
	</body>
</html>
