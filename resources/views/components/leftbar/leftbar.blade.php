@props(['open' => false])
<aside id="default-sidebar" 
	@class(["fixed top-0 left-0 z-40 w-64 h-screen transition-transform",
		'-translate-x-full' => $open, 'sm:-translate-x-full' => $open]) aria-label="Sidebar">
	<div class="h-full px-3 py-4 overflow-y-auto bg-gray-800 light:bg-gray-50">
		<ul class="space-y-2 font-medium">
			{{ $slot }}
		</ul>
	</div>
</aside>