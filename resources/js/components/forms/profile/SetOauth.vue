<template>
	<Card>
		<!-- 
	<div class="setOauth my-10">
	@if(count($this->oauthData) === 0)
	@if (Auth::user()->may_administrate === true)
	<div class="pt-5">
		<h1 class="font-bold text-text-main-100 mb-2 border-b border-solid border-neutral-600 pb-3">{{ __('oauth.NOT_AVAILABLE') }}</h1>
		<p>{{ __('oauth.SET_UP_CREDENTIALS') }}</p>
	</div>
	@endif
	@else
	<div class="pt-5">
		<h1 class="font-bold text-text-main-100 mb-2 border-b border-solid border-neutral-600 pb-3">{{ __('oauth.SET_UP_OAUTH') }}</h1>
	</div>
	@foreach ($this->oauthData as $oauthData) 
		<div class=" text-text-main-100 {{ $oauthData->isEnabled ? '' : 'hover:text-primary-400'}}">
			<i @class(["align-middle w-4",
			"text-create-600" => $oauthData->isEnabled,
			"fa-brands fa-apple" => $oauthData->providerType === 'apple',
			"fa-brands fa-amazon" => $oauthData->providerType === 'amazon',
			"fa-brands fa-facebook" => $oauthData->providerType === 'facebook',
			"fa-brands fa-github" => $oauthData->providerType === 'github',
			"fa-brands fa-google" => $oauthData->providerType === 'google',
			"fa-brands fa-mastodon" => $oauthData->providerType === 'mastodon',
			"fa-brands fa-microsoft" => $oauthData->providerType === 'microsoft',
			"fa-solid fa-cloud" => $oauthData->providerType === 'nextcloud',
			"fa-solid fa-key" => $oauthData->providerType === 'keycloak',
			])></i>
			@if($oauthData->isEnabled)
			<span class="ml-2">
				{{ sprintf(__('oauth.TOKEN_REGISTERED'), ucfirst($oauthData->providerType)) }}<a wire:click='clear("{{ $oauthData->providerType }}")' class='ml-2 cursor-pointer italic text-text-main-400 hover:text-danger-800'>({{ __('oauth.RESET') }})</a>
			</span>
			@else
				<a href="{{ $oauthData->registrationRoute }}"
					class = 'ml-2 cursor-pointer'>
					{{ sprintf(__('oauth.SET_UP'), ucfirst($oauthData->providerType)) }}
				</a>
			@endif
		</div>
	@endforeach
	@endif
</div> -->
	</Card>
</template>
<script setup lang="ts">
import Card from "primevue/card";
</script>
