<div style="display: grid;grid-template-columns: 1fr 1fr 1fr;">
	<div></div>
	<div>
	@php
	$same = true;
	$data1_array = explode("\n",$data1);
	$data2_array = explode("\n",$data2);
	@endphp
	@for ($i = 0; $i < count($data1_array) && $i < count($data2_array); $i++)
		@if ($data1_array[$i] !== $data2_array[$i])
			<pre style="color:darkgreen; margin-bottom: 0">{{ $i }} - {{ $data1_array[$i] }}</pre>
			<pre style="color:darkred; margin-top: 0">{{ $i }} + {{ $data2_array[$i] }}</pre>
			@php
			$same = false
			@endphp
		@endif
	@endfor
	@if ($same)
		<pre style="font-weight: bold; color:green; text-align:center;">Identical content</pre>
	@endif
	</div>
	<div></div>
</div>
<div style="display: grid;grid-template-columns: 1fr 1fr 1fr 1fr;">
	<div></div>
	<pre>{{ $data1 }}</pre>
	<pre>{{ $data2 }}</pre>
	<div></div>
</div>