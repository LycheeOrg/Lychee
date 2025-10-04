<template>
	<Dialog v-model:visible="visible" modal :dismissable-mask="true" pt:root:class="border-none" @hide="closeCallback">
		<template #container="{ closeCallback }">
			<div class="flex flex-col gap-4 bg-gradient-to-b from-bg-300 to-bg-400 relative max-w-2xl w-full rounded-md text-muted-color">
				<div v-if="importing">
					<div class="p-9 flex flex-col items-center justify-center gap-4">
						<i class="pi pi-spin pi-spinner text-4xl text-primary-500" />
						<p class="text-base text-muted-color-emphasis">{{ $t("import_from_server.importing_please_be_patient") }}</p>
					</div>
					<Button severity="secondary" class="w-full font-bold border-none rounded-none rounded-b-xl" @click="closeCallback">
						{{ $t("dialogs.button.close") }}
					</Button>
				</div>
				<div class="p-9 flex flex-col" v-else-if="importState.options">
					<h2 class="text-muted-color-emphasis font-bold text-lg mb-2">{{ $t("import_from_server.title") }}</h2>
					<p class="mb-5 text-base">
						{{ $t("import_from_server.description") }}
					</p>
					<div class="mb-2">
						<span class="text-muted-color-emphasis">{{ $t("import_from_server.selected_directory") }}</span>
						<pre class="text-sm font-mono w-full overflow-clip">{{ directory }}</pre>
					</div>
					<div class="flex flex-col text-xs max-h-48 overflow-y-scroll">
						<div
							v-for="dir in directories"
							@click="onBrowse(dir)"
							:key="`${directory}/${dir}`"
							class="cursor-pointer hover:bg-primary-500/10 p-0.5 bg-surface-900/25 dark:border-t-surface-700/50 border-t first:border-none font-mono"
						>
							{{ dir }}
						</div>
					</div>
					<div class="flex flex-col gap-2 mt-4">
						<form>
							<div class="flex items-center gap-2">
								<ToggleSwitch
									inputId="delete_imported"
									v-model="importState.options.delete_imported"
									class="cursor-pointer"
									:style="danger_style"
								/>
								<span v-if="importState.options.delete_imported" class="pi pi-exclamation-triangle text-danger-600 text-xs"></span>
								<label for="delete_imported" class="cursor-pointer text-sm">{{ $t("import_from_server.delete_imported") }}</label>
							</div>
							<div class="flex items-center gap-2">
								<ToggleSwitch inputId="import_via_symlink" v-model="importState.options.import_via_symlink" class="cursor-pointer" />
								<label for="import_via_symlink" class="cursor-pointer text-sm">{{
									$t("import_from_server.import_via_symlink")
								}}</label>
							</div>
							<div class="flex items-center gap-2">
								<ToggleSwitch inputId="skip_duplicates" v-model="importState.options.skip_duplicates" class="cursor-pointer" />
								<label for="skip_duplicates" class="cursor-pointer text-sm">{{ $t("import_from_server.skip_duplicates") }}</label>
							</div>
							<div class="flex items-center gap-2">
								<ToggleSwitch inputId="resync_metadata" v-model="importState.options.resync_metadata" class="cursor-pointer" />
								<label for="resync_metadata" class="cursor-pointer text-sm">{{ $t("import_from_server.resync_metadata") }}</label>
							</div>
							<div class="flex items-center gap-2">
								<ToggleSwitch
									inputId="delete_missing_photos"
									v-model="importState.options.delete_missing_photos"
									class="cursor-pointer"
									:style="warning_style"
								/>
								<span
									v-if="importState.options.delete_missing_photos"
									class="pi pi-exclamation-triangle text-orange-600 text-xs"
								></span>
								<label for="delete_missing_photos" class="cursor-pointer text-sm">{{
									$t("import_from_server.delete_missing_photos")
								}}</label>
							</div>
							<div class="flex items-center gap-2">
								<ToggleSwitch
									inputId="delete_missing_albums"
									v-model="importState.options.delete_missing_albums"
									class="cursor-pointer"
									:style="warning_style"
								/>
								<span
									v-if="importState.options.delete_missing_albums"
									class="pi pi-exclamation-triangle text-orange-600 text-xs"
								></span>
								<label for="delete_missing_albums" class="cursor-pointer text-sm">{{
									$t("import_from_server.delete_missing_albums")
								}}</label>
							</div>
						</form>
					</div>
				</div>
				<div v-else class="p-9">
					{{ $t("import_from_server.loading") }}
				</div>
				<div class="flex justify-center" v-if="!importing && importState.options">
					<Button
						severity="secondary"
						class="w-full font-bold border-none rounded-none ltr:rounded-bl-xl rtl:rounded-br-xl"
						@click="closeCallback"
					>
						{{ $t("dialogs.button.cancel") }}
					</Button>
					<Button severity="contrast" class="w-full font-bold border-none rounded-none ltr:rounded-br-xl rtl:rounded-bl-xl" @click="submit">
						{{ $t("import_from_server.sync") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import { useRouter } from "vue-router";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import { onMounted, Ref, ref, watch } from "vue";
import ImportService, { ImportFromServerRequest } from "@/services/import-service";
import AlbumService from "@/services/album-service";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import { useToast } from "primevue/usetoast";
import ToggleSwitch from "primevue/toggleswitch";
import { useImportState } from "@/stores/ImportState";

const visible = defineModel("visible", { default: false }) as Ref<boolean>;
const emits = defineEmits<{ refresh: [] }>();
const toast = useToast();

const router = useRouter();
const { getParentId } = usePhotoRoute(router);

const importState = useImportState();
const directory = ref<string>("");
const importing = ref<boolean>(false);

const directories = ref<string[]>([]);

// We saved previously browsed directories to avoid reloading them
const browsed = ref<Map<string, string[]>>(new Map<string, string[]>());

function browse(dir: string) {
	if (browsed.value.has(dir)) {
		// We have validated that the key exists, so the cast as is safe
		directories.value = browsed.value.get(dir) as string[];
		console.log("Using cached browse for", dir, directories.value);
		return;
	}

	ImportService.browse(dir).then((response) => {
		directories.value = response.data;
		browsed.value.set(dir, response.data);
	});
}

function onBrowse(dir: string) {
	// Clear current directories immediately to avoid confusion
	if (dir === "..") {
		const parts = directory.value.split("/");
		parts.pop();
		directory.value = parts.join("/");
		directory.value = normalize(directory.value);
		console.log("cd .. to", directory.value);
	} else {
		directory.value = normalize(directory.value);
		directory.value = directory.value + "/" + dir;
	}
	browse(directory.value);
}

function normalize(path: string): string {
	if (path === "") {
		return "/";
	}
	if (path === "/") {
		return "";
	}
	return path;
}

function submit() {
	if (!importState.options) {
		return;
	}

	const payload: ImportFromServerRequest = {
		album_id: getParentId() ?? null,
		directories: [directory.value],
		delete_imported: importState.options.delete_imported,
		import_via_symlink: importState.options.import_via_symlink,
		skip_duplicates: importState.options.skip_duplicates,
		resync_metadata: importState.options.resync_metadata,
		delete_missing_photos: importState.options.delete_missing_photos,
		delete_missing_albums: importState.options.delete_missing_albums,
	};

	importing.value = true;
	ImportService.importFromServer(payload).then(() => {
		directory.value = "";
		visible.value = false;
		toast.add({ severity: "success", summary: "Success", detail: "Import started successfully", life: 3000 });
		// Clear cache for the parent album to ensure the new photos are displayed
		AlbumService.clearCache();
		importing.value = false;
		emits("refresh");
	});
}

function closeCallback() {
	visible.value = false;
}

onMounted(() => {
	importState.load();
});

watch(
	() => visible.value,
	(newVal) => {
		if (newVal) {
			importState.load().then(() => {
				// reset state
				directory.value = importState.directory;
				browse(directory.value);
			});
		}
	},
);

const danger_style =
	"--p-toggleswitch-checked-background: var(--p-red-800);--p-toggleswitch-checked-hover-background: var(--p-red-900);--p-toggleswitch-hover-background: var(--p-red-900);";
const warning_style =
	"--p-toggleswitch-checked-background: var(--p-orange-700);--p-toggleswitch-checked-hover-background: var(--p-orange-800);--p-toggleswitch-hover-background: var(--p-orange-800);";
</script>
