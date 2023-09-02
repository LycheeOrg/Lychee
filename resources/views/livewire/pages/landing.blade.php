<main class="w-full bg-black roboto">
    {{-- @once --}}
    {{-- <link type="text/css" rel="stylesheet" href="{{ URL::asset(Helpers::cacheBusting('dist/landing.css')) }}"> --}}
    {{-- <script type="text/javascript" src="{{ URL::asset(Helpers::cacheBusting('dist/landing.js')) }}"></script> --}}
    {{-- @endonce --}}

    <div id="header"
        class="fixed top-0 left-0 right-0 z-50 opacity-100 transition-none duration-1000 
	{{-- translate-y-64 --}}
	animate animate-down">
        <div id="logo" class=" float-left p-4 text-white">
            <a href="#">
                <h1 class="text-lg font-bold uppercase text-center roboto">{{ $title }}
                    <span class="p-0 text-2xs block font-thin tracking-wide leading-[0]">{{ $subtitle }}</span>
                </h1>
            </a>
        </div>
    </div>

    <div id="menu_wrap"
        class="fixed top-0 right-0 z-50 w-4/5 opacity-100 transition-none duration-1000 
	{{-- translate-y-64 --}}
	animate animate-down">
        <div id="menu" class="w-full">
            <ul class="menu list-none">
                <li class="menu-item relative block float-right pt-6 pb-5 px-3">
                    {{-- Here we can also use livewire to directly open the gallery without reloading the full page --}}
                    <a href="{{ route('livewire-gallery') }}" class="block text-xs uppercase font-normal text-white hover:text-neutral-400 "
                        wire:navigate>{{ __('lychee.GALLERY') }}</a>
                </li>
            </ul>
        </div>
    </div>

    {{-- <div id="intro">
		<div id="intro_content">
			<h1 class="animate_slower pop-in">{{ $title }}</h1>
			<h2><span class="animate_slower pop-in">{{ $subtitle }}</span></h2>
		</div>
	</div> --}}

    <div id="slides" class="absolute left-0 top-0 w-screen h-[98vh] opacity-100 scale-100 transition-all ease-in-out duration-[2s]
	animate_slower pop-in-last">
        <div class="slides-container w-full h-full">
            <ul class="list-none">
                <li class="w-full h-full">
                    {{-- <div class="overlay"></div> --}}
                    <img class="w-full h-full object-cover absolute top-0 left-0" src="{{ $background }}" alt="landing image" />
                </li>
            </ul>
        </div>
    </div>
    <x-footer layout="footer-landing" />

    {{-- <livewire:components.footer :class="'animate animate-up toggled'" :html_id="'footer'" /> --}}
    </div>
