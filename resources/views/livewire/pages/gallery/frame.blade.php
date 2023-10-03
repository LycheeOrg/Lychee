<div class="w-full" x-data="{
		refreshTimeout: @js($timeout),
		imgSrc: @js($src),
		imgSrcset: @js($srcset),

		start() {
			$refs.shutter.classList.add('opacity-0');
			setTimeout(() => this.rotate(), 1000 * this.refreshTimeout);
		},

		async rotate() {
			let newPhotos = await $wire.loadPhoto();
			$refs.shutter.classList.remove('opacity-0');
			setTimeout(() => this.changePhoto(newPhotos), 1000);
			setTimeout(() => this.rotate(), 1000 * this.refreshTimeout);
		},

		changePhoto(newPhotos) {
			this.imgSrc = newPhotos.src;
			this.imgSrcset = newPhotos.srcset;
			$refs.shutter.classList.add('opacity-0');
		}
	}"
    x-init="start()"
    >
	<div class="h-screen w-screen">
		<img 
			alt="image background" class="absolute w-screen h-screen object-cover blur-lg object-center" :src="imgSrc">
		<div class="w-screen h-screen flex justify-center items-center flex-wrap bg-repeat bg-noise">
			<img alt="Random Image" class="h-[95%] w-[95%] object-contain filter drop-shadow-black"
				:src="imgSrc" :srcset="imgSrcset">
		</div>
		<div x-ref="shutter"
			class="absolute w-screen h-screen bg-dark-900 transition-opacity duration-1000 ease-in-out top-0 left-0">
		</div>
	</div>
</div>
