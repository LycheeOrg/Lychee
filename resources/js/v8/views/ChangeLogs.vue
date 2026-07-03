<template>
	<div class="w-full border-0 h-14 flex items-center justify-between px-2">
		<OpenLeftMenu />
		<span class="absolute left-1/2 -translate-x-1/2">{{ $t("changelogs.title") }}</span>
	</div>
	<UCard class="max-w-3xl mx-auto my-12 text-muted" :ui="{ header: 'hidden' }">
		<div v-html="$t('changelogs.description')"></div>
	</UCard>
	<UCard v-for="(changeLog, index) in changeLogs" :key="'cl' + index" class="max-w-3xl mx-auto my-12" :ui="{ header: 'hidden' }">
		<h2 class="text-4xl font-bold text-highlighted">{{ changeLog.version }}</h2>
		<p class="text-sm text-gray-500">
			{{ changeLog.date }}
		</p>
		<div
			class="mt-4 prose max-w-none dark:prose-invert prose-blockquote:text-muted prose-blockquote:m-0 prose-blockquote:text-base prose-code:before:content-[''] prose-code:after:content-[''] prose-li:m-0 prose-a:text-primary prose-a:hover:text-primary prose-a:no-underline"
			v-html="changeLog.changes"
		></div>
	</UCard>
</template>
<script setup lang="ts">
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import InitService from "@/services/init-service";
import { ref } from "vue";

const changeLogs = ref<App.Http.Resources.Diagnostics.ChangeLogInfo[] | undefined>(undefined);

InitService.fetchChangeLog()
	.then((response) => {
		changeLogs.value = response.data;
	})
	.catch((error) => {
		console.error("Error fetching change logs:", error);
	});
</script>
