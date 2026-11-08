/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */
import { afterEach, beforeEach, describe, expect, it, vi } from "vite-plus/test";
import { flushPromises, mount } from "@vue/test-utils";
import { ref } from "vue";
import LoginV7 from "@/v7/components/forms/auth/LoginForm.vue";
import LoginV8 from "@/v8/components/forms/auth/LoginForm.vue";

const mocks = vi.hoisted(() => ({ login: vi.fn(), clear: vi.fn(), refresh: vi.fn() }));
vi.mock("@/services/auth-service", () => ({ default: { login: mocks.login } }));
vi.mock("laravel-vue-i18n", () => ({ trans: (key: string) => key }));
vi.mock("@/composables/useClearViewerState", () => ({ useClearViewerState: () => ({ clearViewerState: mocks.clear }) }));
vi.mock("@/stores/UserState", () => ({ useUserStore: () => ({ getOauthData: async () => [] }) }));
vi.mock("@/stores/LycheeState", () => ({
	useLycheeStateStore: () => ({
		is_basic_auth_enabled: ref(true),
		is_webauthn_enabled: ref(true),
		is_email_login_enabled: ref(false),
		is_se_enabled: ref(false),
		is_white_label_enabled: ref(false),
		is_init: true,
		refresh: mocks.refresh,
	}),
}));
vi.mock("@/stores/ModalsState", () => ({ useTogglablesStateStore: () => ({ is_login_open: ref(true), is_webauthn_open: ref(false) }) }));

const wrappers: ReturnType<typeof mount>[] = [];
beforeEach(() => {
	vi.clearAllMocks();
	mocks.refresh.mockResolvedValue(undefined);
});
afterEach(() => {
	wrappers.splice(0).forEach((wrapper) => wrapper.unmount());
});

const button = { template: '<button type="button"><slot /></button>' };
const alert = { props: ["description"], template: '<div role="alert">{{ description }}<slot /></div>' };
const input = { template: "<input />" };

describe.each([
	["v7", LoginV7],
	["v8", LoginV8],
] as const)("%s password login", (_version, component) => {
	async function setup() {
		const wrapper = mount(component, {
			global: {
				mocks: { $t: (key: string) => key },
				directives: { focustrap: {} },
				stubs: {
					Button: button,
					UButton: button,
					Message: alert,
					UAlert: alert,
					InputText: input,
					InputPassword: input,
					UInput: input,
					Checkbox: true,
					UCheckbox: true,
					FloatLabel: { template: "<div><slot /></div>" },
					UFormField: { template: "<div><slot /></div>" },
					UIcon: true,
				},
			},
		});
		wrappers.push(wrapper);
		await flushPromises();
		return wrapper;
	}
	async function submit(wrapper: Awaited<ReturnType<typeof setup>>) {
		await wrapper.get("button").trigger("click");
		await flushPromises();
	}
	it("explains 429 inline without treating it as invalid credentials", async () => {
		mocks.login.mockRejectedValue({ response: { status: 429 } });
		const wrapper = await setup();
		await submit(wrapper);
		expect(wrapper.text()).toContain("dialogs.gallery_unlock.too_many_attempts");
		expect(wrapper.text()).not.toContain("dialogs.login.unknown_invalid");
		expect(mocks.clear).not.toHaveBeenCalled();
		expect(wrapper.emitted("logged-in")).toBeUndefined();
	});
	it.each([{ response: { status: 500 } }, new Error("Network unavailable")])("does not mislabel other failures as throttling", async (error) => {
		mocks.login.mockRejectedValue(error);
		const wrapper = await setup();
		await submit(wrapper);
		expect(wrapper.text()).not.toContain("dialogs.gallery_unlock.too_many_attempts");
		expect(wrapper.text()).not.toContain("dialogs.login.unknown_invalid");
		expect(mocks.clear).not.toHaveBeenCalled();
	});
	it("keeps invalid credentials feedback for 401", async () => {
		mocks.login.mockRejectedValue({ response: { status: 401 } });
		const wrapper = await setup();
		await submit(wrapper);
		expect(wrapper.text()).toContain("dialogs.login.unknown_invalid");
	});
	it("clears stale feedback on the next attempt and succeeds", async () => {
		mocks.login.mockRejectedValueOnce({ response: { status: 429 } });
		const wrapper = await setup();
		await submit(wrapper);
		expect(wrapper.text()).toContain("dialogs.gallery_unlock.too_many_attempts");
		let finish!: () => void;
		mocks.login.mockReturnValueOnce(
			new Promise<void>((resolve) => {
				finish = resolve;
			}),
		);
		await submit(wrapper);
		expect(wrapper.text()).not.toContain("dialogs.gallery_unlock.too_many_attempts");
		finish();
		await flushPromises();
		expect(mocks.clear).toHaveBeenCalledOnce();
		expect(wrapper.emitted("logged-in")).toHaveLength(1);
	});
	it("clears throttling feedback when switching to WebAuthn", async () => {
		mocks.login.mockRejectedValue({ response: { status: 429 } });
		const wrapper = await setup();
		await submit(wrapper);
		await wrapper.get('[title="WebAuthn"]').trigger("click");
		expect(wrapper.text()).not.toContain("dialogs.gallery_unlock.too_many_attempts");
	});
});
