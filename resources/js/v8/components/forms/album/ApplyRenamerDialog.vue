<template>
	<UModal v-model:open="open">
		<template #body>
			<!-- Step 1: Rule Selection -->
			<div v-if="step === 'select'" class="text-center text-muted">
				<p class="text-sm/8 font-bold mb-4">{{ $t("dialogs.apply_renamer.title") }}</p>
				<p class="text-sm/8 mb-4">{{ $t("dialogs.apply_renamer.description") }}</p>

				<div v-if="is_loading_rules" class="flex justify-center py-4">
					<Spinner class="text-2xl" />
				</div>
				<div v-else-if="rules.length === 0" class="py-4 text-sm text-muted">
					{{ $t("dialogs.apply_renamer.no_rules") }}
				</div>
				<div v-else class="text-left space-y-2 max-h-60 overflow-y-auto">
					<div v-for="rule in rules" :key="rule.id" class="p-2 rounded hover:bg-elevated">
						<UCheckbox v-model="selected_rule_ids" :value="rule.id" class="w-full">
							<template #label>
								<div class="font-semibold text-sm">{{ rule.rule }}</div>
								<div class="text-xs text-muted mt-1 space-y-0.5">
									<div class="flex gap-2">
										<span class="font-mono bg-elevated px-1.5 py-0.5 rounded">{{ rule.mode }}</span>
										<span v-if="['first', 'all', 'regex', 'trim'].includes(rule.mode)">
											<span class="text-error">"{{ rule.needle }}"</span>
											<span class="mx-1">→</span>
											<span class="text-success">"{{ rule.replacement }}"</span>
										</span>
									</div>
									<div v-if="rule.description" class="text-muted/80">{{ rule.description }}</div>
								</div>
							</template>
						</UCheckbox>
					</div>
				</div>

				<div class="mt-4 flex gap-2">
					<div class="flex items-center gap-2">
						<label class="text-xs">{{ $t("dialogs.apply_renamer.target") }}</label>
						<UFieldGroup>
							<UButton
								v-for="option in target_options"
								:key="option.value"
								size="xs"
								:color="target === option.value ? 'primary' : 'neutral'"
								:variant="target === option.value ? 'solid' : 'outline'"
								@click="
									() => {
										target = option.value;
									}
								"
							>
								{{ option.label }}
							</UButton>
						</UFieldGroup>
					</div>
					<div class="flex items-center gap-2">
						<label class="text-xs">{{ $t("dialogs.apply_renamer.scope") }}</label>
						<UFieldGroup>
							<UButton
								v-for="option in scope_options"
								:key="option.value"
								size="xs"
								:color="scope === option.value ? 'primary' : 'neutral'"
								:variant="scope === option.value ? 'solid' : 'outline'"
								@click="
									() => {
										scope = option.value;
									}
								"
							>
								{{ option.label }}
							</UButton>
						</UFieldGroup>
					</div>
				</div>
			</div>

			<!-- Step 2: Preview -->
			<div v-else-if="step === 'preview'" class="text-center text-muted">
				<p class="text-sm/8 font-bold mb-4">{{ $t("dialogs.apply_renamer.preview_title") }}</p>

				<div v-if="is_loading_preview" class="flex justify-center py-4">
					<Spinner class="text-2xl" />
				</div>
				<div v-else-if="preview_items.length === 0" class="py-4 text-sm text-muted">
					{{ $t("dialogs.apply_renamer.no_changes") }}
				</div>
				<div v-else class="text-left max-h-80 overflow-y-auto">
					<table class="w-full text-sm">
						<thead>
							<tr class="border-b border-default">
								<th class="text-left py-1 px-2">{{ $t("dialogs.apply_renamer.original") }}</th>
								<th class="text-left py-1 px-2">{{ $t("dialogs.apply_renamer.new_title") }}</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="item in preview_items" :key="item.id" class="border-b border-muted">
								<td class="py-1 px-2 text-error line-through">{{ item.original }}</td>
								<td class="py-1 px-2 text-success">{{ item.new }}</td>
							</tr>
						</tbody>
					</table>
					<p class="text-xs mt-2 text-muted">
						{{ sprintf($t("dialogs.apply_renamer.count_changes"), preview_items.length) }}
					</p>
				</div>
			</div>
		</template>
		<template #footer>
			<UButton color="neutral" variant="ghost" class="font-bold w-full justify-center" @click="handleCancel">
				{{ step === "preview" ? $t("dialogs.apply_renamer.back") : $t("dialogs.button.cancel") }}
			</UButton>
			<UButton
				v-if="step === 'select'"
				color="neutral"
				class="font-bold w-full justify-center"
				:disabled="selected_rule_ids.length === 0"
				@click="loadPreview"
			>
				{{ $t("dialogs.apply_renamer.preview") }}
			</UButton>
			<UButton v-else color="neutral" class="font-bold w-full justify-center" :disabled="preview_items.length === 0" @click="applyRules">
				{{ $t("dialogs.apply_renamer.apply") }}
			</UButton>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { ref, watch } from "vue";
import RenamerService, { type PreviewRenameItem } from "@/services/renamer-service";
import AlbumService from "@/services/album-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import Spinner from "@/v8/components/Spinner.vue";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";

const props = defineProps<{
	albumId?: string;
	photoIds?: string[];
	albumIds?: string[];
}>();

const open = defineModel<boolean>("open", { default: false });

const emits = defineEmits<{
	applied: [];
}>();

const toast = useAppToast();

const step = ref<"select" | "preview">("select");
const rules = ref<App.Http.Resources.Models.RenamerRuleResource[]>([]);
const selected_rule_ids = ref<number[]>([]);
const target = ref<"photos" | "albums">("photos");
const scope = ref<"current" | "descendants">("current");
const is_loading_rules = ref(false);
const is_loading_preview = ref(false);
const preview_items = ref<PreviewRenameItem[]>([]);

const target_options = [
	{ label: trans("dialogs.apply_renamer.photos"), value: "photos" as const },
	{ label: trans("dialogs.apply_renamer.albums"), value: "albums" as const },
];

const scope_options = [
	{ label: trans("dialogs.apply_renamer.current"), value: "current" as const },
	{ label: trans("dialogs.apply_renamer.descendants"), value: "descendants" as const },
];

watch(open, (new_val) => {
	if (new_val) {
		step.value = "select";
		selected_rule_ids.value = [];
		preview_items.value = [];
		loadRules();
	}
});

function loadRules() {
	is_loading_rules.value = true;
	RenamerService.list().then((response) => {
		rules.value = response.data;
		// Select all rules by default
		selected_rule_ids.value = rules.value.map((r) => r.id);
		is_loading_rules.value = false;
	});
}

function loadPreview() {
	is_loading_preview.value = true;
	step.value = "preview";

	RenamerService.preview({
		album_id: props.albumId,
		target: target.value,
		scope: scope.value,
		rule_ids: selected_rule_ids.value,
		photo_ids: props.photoIds,
		album_ids: props.albumIds,
	}).then((response) => {
		preview_items.value = response.data;
		is_loading_preview.value = false;
	});
}

function applyRules() {
	const ids = preview_items.value.map((item) => item.id);

	const data: { photo_ids?: string[]; album_ids?: string[]; rule_ids: number[] } = {
		rule_ids: selected_rule_ids.value,
	};

	if (target.value === "photos") {
		data.photo_ids = ids;
	} else {
		data.album_ids = ids;
	}

	RenamerService.rename(data).then(() => {
		toast.add({
			severity: "success",
			summary: trans("dialogs.apply_renamer.success"),
			life: 3000,
		});
		AlbumService.clearCache(props.albumId);
		close();
		emits("applied");
	});
}

function handleCancel() {
	if (step.value === "preview") {
		step.value = "select";
	} else {
		close();
	}
}

function close() {
	open.value = false;
}
</script>
