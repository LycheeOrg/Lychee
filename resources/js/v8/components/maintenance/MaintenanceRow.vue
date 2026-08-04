<template>
	<div class="flex items-center gap-4 py-3 ltr:flex-row rtl:flex-row-reverse">
		<p class="min-w-0 flex-1">
			<span class="font-semibold text-highlighted"><slot name="title" /></span>
			<span class="text-muted"> – <slot /></span>
		</p>
		<div class="flex shrink-0 items-center gap-2 ltr:justify-end rtl:justify-start">
			<slot name="actions" />
		</div>
	</div>
</template>
