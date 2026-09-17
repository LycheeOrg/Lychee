<template>
	<div class="w-full h-(--header-height) bg-neutral-800 animate-pulse"></div>
</template>
<script setup lang="ts">
// Feature 068 (FR-068-09/NFR-068-07): rendered in a card's header/carousel
// area while `requestCardPhotos()` is in flight; swapped for real content
// on "loaded", or removed entirely (empty carousel, no infinite skeleton)
// on "failed" - both handled by AlbumCardV3.vue's own v-if branching, not
// by this component.
</script>
