<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex">

		<title>Memory Profiler — {{ $trace }}</title>

		<style>
			html, body {
				background-color: #fff;
				color: #2c2c2c;
				font-family: sans-serif;
				margin: 0;
				padding: 24px;
			}

			.header {
				display: flex;
				align-items: center;
				gap: 12px;
				margin-bottom: 16px;
				font-size: 14px;
			}

			.header a {
				color: #0b5ed7;
				text-decoration: none;
			}

			.error-state {
				background-color: #f8d7da;
				border: 1px solid #f5c2c7;
				color: #842029;
				padding: 16px;
				border-radius: 4px;
			}

			.svg-container {
				overflow: auto;
				border: 1px solid #ddd;
				border-radius: 4px;
				padding: 8px;
			}

			.svg-container svg {
				max-width: none;
			}
		</style>
	</head>
	<body>
		<div class="header">
			<a href="{{ route('admin.profiler.index') }}">&larr; Back to trace list</a>
			@if ($meta !== null)
				<span>{{ $meta->route_name ?? $meta->path }} · {{ $meta->created_at }} · {{ \Illuminate\Support\Number::fileSize($meta->peak_memory_bytes, 1) }}</span>
			@else
				<span>{{ $trace }}</span>
			@endif
		</div>

		@if ($error_message !== null)
			<div class="error-state">
				Could not render this trace: {{ $error_message }} See
				<code>docs/specs/2-how-to/enable-memory-profiler.md</code> to install it.
				Raw dump: <a href="{{ route('admin.profiler.download', ['trace' => $trace]) }}">download .pprof</a>
			</div>
		@else
			<div class="svg-container">
				{!! $svg !!}
			</div>
		@endif
	</body>
</html>
