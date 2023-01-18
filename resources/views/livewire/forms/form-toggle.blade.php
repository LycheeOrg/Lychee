<div>
	<p>
		{{ $description }}
		<label class="switch">
			<input wire:model="flag" type="checkbox">
			<span class="slider round"></span>
		</label>
	</p>
	@if($footer !== '')
	<p>
		{!! $footer !!}
	</p>
	@endif
</div>
