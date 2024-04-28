@props(['class' => '', 'has_error' => false])
<div x-data="{ show: true }" class="relative">
<input class="w-full pt-1 pb-0 px-0.5 h-7 border-b border-b-solid border-b-bg-500
placeholder:text-text-main-400
hover:border-b-danger-700 focus:border-b-danger-700
@if($has_error) bg-danger-700/10 text-danger-600 @else text-text-main-0 bg-transparent @endif
{{ $class }}"
{{ $attributes }} 
x-bind:type="show ? 'password' : 'text'"
x-ref="pw" />
	<div class="absolute top-1/2 right-2 cursor-pointer" style="transform: translateY(-50%);">
		<x-icons.iconic class="w-4 h-4 text-text-main-400 hidden" fill="none" x-on:click="show = !show" ::class="{'hidden': !show, 'block':show }" icon="password-eye" />
		<x-icons.iconic class="w-4 h-4 text-text-main-400 hidden" fill="none" x-on:click="show = !show" ::class="{'block': !show, 'hidden':show }" icon="password-eye-closed" />
  </div>
</div>