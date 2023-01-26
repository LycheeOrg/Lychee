<div>
	<p>
		{{ $begin }}
		<span class="select">
			<select wire:model="value1">
			@foreach($this->options1 as $key => $option)
			@if (is_string($key))
				<option value="{{$key}}">{{ $option }}</option>
			@else
				<option>{{ $option }}</option>
			@endif
			@endforeach
			</select>
		</span>
		{{ $middle }}
		<span class="select">
			<select wire:model="value2">
			@foreach($this->options2 as $key => $option)
			@if (is_string($key))
				<option value="{{$key}}">{{ $option }}</option>
			@else
				<option>{{ $option }}</option>
			@endif
			@endforeach
			</select>
		</span>
		{{ $end }}
	</p>
</div>