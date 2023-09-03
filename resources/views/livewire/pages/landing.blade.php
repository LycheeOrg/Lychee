<main id="landing" class="w-screen h-screen bg-black roboto overflow-hidden"
	x-data="{ introVisible: true }"
	x-init="setTimeout(() => introVisible = false, 4000);">
    <div id="header"
        class="fixed top-0 left-0 right-0 z-50 overflow-y-hidden">
        <div id="logo" class=" float-left p-4 text-white translate-y-[-300px] opacity-0 animate-ladningAnimateDown">
            <a href="#">
                <h1 class="text-lg font-bold uppercase text-center roboto">{{ $title }}
                    <span class="p-0 text-2xs block font-thin tracking-wide leading-[0]">{{ $subtitle }}</span>
                </h1>
            </a>
        </div>
    </div>

    <div id="menu_wrap"
        class="fixed top-0 right-0 z-50 w-4/5 overflow-y-hidden">
        <div id="menu" class="w-full translate-y-[-300px] opacity-0 animate-ladningAnimateDown">
            <ul class="menu list-none">
                <li class="menu-item relative block float-right pt-6 pb-5 px-3">
                    {{-- Here we can also use livewire to directly open the gallery without reloading the full page --}}
                    <a href="{{ route('livewire-gallery') }}" class="block text-xs uppercase font-normal text-white hover:text-neutral-400 "
                        wire:navigate.hover >{{ __('lychee.GALLERY') }}</a>
                </li>
            </ul>
        </div>
    </div>

    <div id="intro" :class="introVisible ? '' : 'hidden'"
		class=" z-50 bg-black fixed flex align-middle justify-center left-0 right-0 top-0 bottom-0
			animate-landingIntroFadeOut">
		<div id="intro_content" class=" self-center">
			<h1 class="
				text-center text-2xl  text-white       uppercase font-extralight animate-landingIntroPopIn">{{ $title }}</h1>
			<h2><span class="
				text-center text-base text-neutral-400 uppercase font-extralight animate-landingIntroPopIn">{{ $subtitle }}</span></h2>
		</div>
	</div>

    <div id="slides"
	class="bg-black absolute overflow-hidden left-0 top-0 w-screen h-[98vh] ">
        <div class="slides-container w-full h-full opacity-0 animate-landingSlidesPopIn">
            <ul class="list-none">
                <li class="w-full h-full">
                    {{-- <div class="overlay"></div> --}}
                    <img class="w-full h-full object-cover absolute top-0 left-0" src="{{ $background }}" alt="landing image" />
                </li>
            </ul>
        </div>
    </div>
    <x-footer layout="footer-landing" />
</div>
