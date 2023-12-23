<div>
	<div class="p-9">
		<p class="mb-5 text-neutral-200 text-sm/4">This functionality is no longer available for the following reasons:
			<ul class="pl-5">
				<li class="list-decimal list-outside">Long process required to keep the browser window open.</li>
				<li class="list-decimal list-outside">Sessions time-out were breaking the process.</li>
				<li class="list-decimal list-outside">A more efficient command line alternative is available:<br>
					<pre class="inline-block font-mono text-neutral-200">php artisan lychee:sync</pre></li>
			</ul>
		</p>
	</div>
	<div class="flex w-full box-border">
		<x-forms.buttons.cancel class="border-t border-t-bg-800 rounded-bl-md w-full" wire:click="close">{{ __('lychee.CLOSE') }}</x-forms.buttons.cancel>
	</div>
</div>