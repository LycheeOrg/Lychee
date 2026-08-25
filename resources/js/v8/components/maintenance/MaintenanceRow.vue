<template>
	<div class="flex flex-col gap-1 py-3 md:flex-row md:items-start md:gap-4 rtl:md:flex-row-reverse">
		<div class="flex items-center justify-between gap-4 md:contents">
			<span class="min-w-0 font-semibold text-highlighted"><slot name="title" /></span>
			<div class="flex shrink-0 items-start gap-2 md:order-3 ltr:justify-end rtl:justify-start">
				<slot name="actions" />
			</div>
		</div>
		<span class="text-muted md:min-w-0 md:flex-1"><span class="hidden md:inline">– </span><slot /></span>
	</div>
</template>
