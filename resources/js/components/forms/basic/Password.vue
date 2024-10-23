<template>
	<Password
		v-model="modelValue"
		:promptLabel="props.promptLabel"
		:mediumRegex="props.mediumRegex"
		:strongRegex="props.strongRegex"
		:weakLabel="props.weakLabel"
		:mediumLabel="props.mediumLabel"
		:strongLabel="props.strongLabel"
		:feedback="props.feedback"
		:appendTo="props.appendTo"
		:toggleMask="props.toggleMask"
		:maskIcon="props.maskIcon"
		:unmaskIcon="props.unmaskIcon"
		:invalid="props.invalid"
		:disabled="props.disabled"
		:variant="props.variant"
		:placeholder="props.placeholder"
		:required="props.required"
		:fluid="props.fluid"
		:inputId="props.inputId"
		:inputStyle="props.inputStyle"
		:inputClass="inputClass"
		:inputProps="props.inputProps"
		:panelId="props.panelId"
		:panelClass="props.panelClass"
		:panelStyle="props.panelStyle"
		:panelProps="props.panelProps"
		:overlayId="props.overlayId"
		:overlayClass="props.overlayClass"
		:overlayStyle="props.overlayStyle"
		:overlayProps="props.overlayProps"
		:ariaLabelledby="props.ariaLabelledby"
		:ariaLabel="props.ariaLabel"
		:dt="props.dt"
		:pt="props.pt"
		:ptOptions="props.ptOptions"
		:unstyled="props.unstyled"
	/>
</template>
<script setup lang="ts">
import { HTMLAttributes, InputHTMLAttributes, ref } from "vue";
import type { DesignToken, HintedString, Nullable, PassThrough } from "@primevue/core";
import Password, { PasswordPassThroughOptions } from "primevue/password";
import type { PassThroughOptions } from "primevue/passthrough";

const props = defineProps<{
	/**
	 * Value of the component.
	 */
	modelValue?: Nullable<string>;
	/**
	 * Text to prompt password entry. Defaults to PrimeVue Locale configuration.
	 */
	promptLabel?: string | undefined;
	/**
	 * Regex for a medium level password.
	 * @defaultValue ^(((?=.*[a-z])(?=.*[A-Z]))|((?=.*[a-z])(?=.*[0-9]))|((?=.*[A-Z])(?=.*[0-9])))(?=.{6,})
	 */
	mediumRegex?: string | RegExp | undefined;
	/**
	 * Regex for a strong level password.
	 * @defaultValue ^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.{8,})
	 */
	strongRegex?: string | RegExp | undefined;
	/**
	 * Text for a weak password. Defaults to PrimeVue Locale configuration.
	 */
	weakLabel?: string | undefined;
	/**
	 * Text for a medium password. Defaults to PrimeVue Locale configuration.
	 */
	mediumLabel?: string | undefined;
	/**
	 * Text for a strong password. Defaults to PrimeVue Locale configuration.
	 */
	strongLabel?: string | undefined;
	/**
	 * Whether to show the strength indicator or not.
	 * @defaultValue true
	 */
	feedback?: boolean | undefined;
	/**
	 * A valid query selector or an HTMLElement to specify where the overlay gets attached.
	 * @defaultValue body
	 */
	appendTo?: HintedString<"body" | "self"> | undefined | HTMLElement;
	/**
	 * Whether to show an icon to display the password as plain text.
	 * @defaultValue false
	 */
	toggleMask?: boolean | undefined;
	/**
	 * Icon to hide displaying the password as plain text.
	 */
	maskIcon?: string | undefined;
	/**
	 * Icon to show displaying the password as plain text.
	 */
	unmaskIcon?: string | undefined;
	/**
	 * When present, it specifies that the component should have invalid state style.
	 * @defaultValue false
	 */
	invalid?: boolean | undefined;
	/**
	 * When present, it specifies that the component should be disabled.
	 * @defaultValue false
	 */
	disabled?: boolean | undefined;
	/**
	 * Specifies the input variant of the component.
	 * @defaultValue outlined
	 */
	variant?: "outlined" | "filled" | undefined;
	/**
	 * Placeholder text for the input.
	 */
	placeholder?: string | undefined;
	/**
	 * When present, it specifies that an input field must be filled out before submitting the form.
	 * @defaultValue false
	 */
	required?: boolean | undefined;
	/**
	 * Spans 100% width of the container when enabled.
	 * @defaultValue null
	 */
	fluid?: boolean | undefined;
	/**
	 * Identifier of the underlying input element.
	 */
	inputId?: string | undefined;
	/**
	 * Inline style of the input field.
	 */
	inputStyle?: object | undefined;
	/**
	 * Style class of the input field.
	 */
	inputClass?: string | object | undefined;
	/**
	 * Used to pass all properties of the HTMLInputElement to the focusable input element inside the component.
	 */
	inputProps?: InputHTMLAttributes | undefined;
	/**
	 * Identifier of the underlying overlay panel element.
	 */
	panelId?: string | undefined;
	/**
	 * Style class of the overlay panel.
	 */
	panelClass?: string | object | undefined;
	/**
	 * Inline style of the overlay panel.
	 */
	panelStyle?: object | undefined;
	/**
	 * Used to pass all properties of the HTMLDivElement to the overlay panel inside the component.
	 */
	panelProps?: HTMLAttributes | undefined;
	/**
	 * Identifier of the underlying overlay element.
	 */
	overlayId?: string | undefined;
	/**
	 * Style class of the overlay.
	 */
	overlayClass?: string | object | undefined;
	/**
	 * Inline style of the overlay.
	 */
	overlayStyle?: object | undefined;
	/**
	 * Used to pass all properties of the HTMLDivElement to the overlay inside the component.
	 */
	overlayProps?: HTMLAttributes | undefined;
	/**
	 * Establishes relationships between the component and label(s) where its value should be one or more element IDs.
	 */
	ariaLabelledby?: string | undefined;
	/**
	 * Establishes a string value that labels the component.
	 */
	ariaLabel?: string | undefined;
	/**
	 * It generates scoped CSS variables using design tokens for the component.
	 */
	dt?: DesignToken<any>;
	/**
	 * Used to pass attributes to DOM elements inside the component.
	 * @type {PasswordPassThroughOptions}
	 */
	pt?: PassThrough<PasswordPassThroughOptions>;
	/**
	 * Used to configure passthrough(pt) options of the component.
	 * @type {PassThroughOptions}
	 */
	ptOptions?: PassThroughOptions;
	/**
	 * When enabled, it removes component related styles in the core.
	 * @defaultValue false
	 */
	unstyled?: boolean;
}>();

// const props = defineProps<{
// 	size?: "small" | "large" | undefined;
// 	invalid?: boolean | undefined;
// 	variant?: "outlined" | "filled" | undefined;
// 	fluid?: boolean;
// 	dt?: DesignToken<any>;
// 	pt?: PassThrough<InputTextPassThroughOptions>;
// 	ptOptions?: PassThroughOptions;
// 	unstyled?: boolean;
// 	class?: string;
// }>();

const modelValue = defineModel<Nullable<string>>();
const inputClass = ref((props.inputClass ?? "") + " border-0 p-3 w-full border-b hover:border-b-danger-600 focus:border-b-danger-600");
</script>
