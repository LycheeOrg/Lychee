import { Preset } from "@primeuix/themes/types";

const LycheePrimeVueConfig: Preset = {
	semantic: {
		focusRing: {
			width: "1px",
			style: "solid",
			color: "{primary.color}",
			offset: "2px",
			shadow: "none",
		},
		disabledOpacity: "0.6",
		iconSize: "1rem",
		anchorGutter: "2px",
		primary: {
			50: "{sky.50}",
			100: "{sky.100}",
			200: "{sky.200}",
			300: "{sky.300}",
			400: "{sky.400}",
			500: "{sky.500}",
			600: "{sky.600}",
			700: "{sky.700}",
			800: "{sky.800}",
			900: "{sky.900}",
			950: "{sky.950}",
		},
		formField: {
			paddingX: "0.75rem",
			paddingY: "0.5rem",
			borderRadius: "{border.radius.md}",
			focusRing: {
				width: "0",
				style: "none",
				offset: "0",
				shadow: "none",
			},
		},
		navigation: {
			list: { gap: 0 },
		},
	},
	colorScheme: {
		light: {
			surface: {
				0: "#ffffff",
				50: "{slate.50}",
				100: "{slate.100}",
				200: "{slate.200}",
				300: "{slate.300}",
				400: "{slate.400}",
				500: "{slate.500}",
				600: "{slate.600}",
				700: "{slate.700}",
				800: "{slate.800}",
				900: "{slate.900}",
				950: "{slate.950}",
			},
			primary: {
				color: "{primary.500}",
				contrastColor: "#ffffff",
				hoverColor: "{primary.600}",
				activeColor: "{primary.700}",
			},
			highlight: {
				background: "{primary.50}",
				focusBackground: "{primary.100}",
				color: "{primary.700}",
				focusColor: "{primary.800}",
			},
			formField: {
				// background: '{surface.0}',
				disabledBackground: "{surface.200}",
				filledBackground: "{surface.50}",
				filledFocusBackground: "{surface.50}",
				// borderColor: '{surface.300}',
				hoverBorderColor: "{surface.400}",
				focusBorderColor: "{primary.color}",
				invalidBorderColor: "{red.400}",
				color: "{surface.700}",
				disabledColor: "{surface.500}",
				placeholderColor: "{surface.500}",
				floatLabelColor: "{surface.500}",
				floatLabelFocusColor: "{surface.500}",
				floatLabelInvalidColor: "{red.400}",
				iconColor: "{surface.400}",
				shadow: "0 0 #0000, 0 0 #0000, 0 1px 2px 0 rgba(18, 18, 23, 0.05)",
			},
			text: {
				color: "{surface.700}",
				hoverColor: "{surface.800}",
				mutedColor: "{surface.500}",
				hoverMutedColor: "{surface.600}",
			},
			content: {
				background: "{surface.0}",
				hoverBackground: "{surface.100}",
				borderColor: "{surface.200}",
				color: "{text.color}",
				hoverColor: "{text.hover.color}",
			},
		},
		dark: {
			surface: {
				0: "#ffffff",
				50: "{zinc.50}",
				100: "{zinc.100}",
				200: "{zinc.200}",
				300: "{zinc.300}",
				400: "{zinc.400}",
				500: "{zinc.500}",
				600: "{zinc.600}",
				700: "{zinc.700}",
				800: "{zinc.800}",
				900: "{zinc.900}",
				950: "{zinc.950}",
			},
			primary: {
				color: "{primary.400}",
				contrastColor: "{surface.900}",
				hoverColor: "{primary.300}",
				activeColor: "{primary.200}",
			},
			highlight: {
				background: "color-mix(in srgb, {primary.400}, transparent 84%)",
				focusBackground: "color-mix(in srgb, {primary.400}, transparent 76%)",
				color: "rgba(255,255,255,.87)",
				focusColor: "rgba(255,255,255,.87)",
			},
			formField: {
				background: "{surface.950}",
				disabledBackground: "{surface.700}",
				filledBackground: "{surface.800}",
				filledFocusBackground: "{surface.800}",
				borderColor: "{surface.700}",
				hoverBorderColor: "{surface.600}",
				focusBorderColor: "{primary.color}",
				invalidBorderColor: "{red.300}",
				color: "{surface.0}",
				disabledColor: "{surface.400}",
				placeholderColor: "{surface.300}",
				floatLabelColor: "{surface.300}",
				floatLabelFocusColor: "{surface.300}",
				floatLabelInvalidColor: "{red.300}",
				iconColor: "{surface.400}",
				shadow: "0 0 #0000, 0 0 #0000, 0 1px 2px 0 rgba(18, 18, 23, 0.05)",
			},
			text: {
				color: "{surface.0}",
				hoverColor: "{surface.0}",
				mutedColor: "{surface.400}",
				hoverMutedColor: "{surface.200}",
			},
			select: {
				color: "{red.400}",
			},
			content: {
				background: "{surface.900}",
				hoverBackground: "{surface.800}",
				borderColor: "{surface.700}",
				color: "{text.color}",
				hoverColor: "{text.hover.color}",
			},
		},
	},
	components: {
		autocomplete: {
			option: {
				padding: "0 1rem",
			},
			colorScheme: {
				dark: {
					root: {
						background: "transparent",
						color: "{surface.300}",
						borderRadius: "0",
						borderColor: "transparent",
						hoverBorderColor: "transparent",
					},
					dropdown: {
						background: "{surface.800}",
						borderColor: "{surface.700}",
						color: "{surface.300}",
						hoverBackground: "{surface.700}",
						hoverColor: "{surface.200}",
					},
					option: {
						color: "{surface.400}",
						focusBackground: "linear-gradient({primary.500}, {primary.600})",
						focusColor: "{surface.0}",
					},
					overlay: {
						background: "{surface.900}",
						borderColor: "{surface.800}",
					},
				},
			},
		},
		chip: {
			colorScheme: {
				dark: {
					root: {
						background: "{surface.900}",
					},
				},
			},
		},
		button: {
			root: {
				paddingX: "0.75rem",
				paddingY: "0.75rem",
			},
			colorScheme: {
				light: {
					root: {
						contrast: {
							color: "{primary.500}",
							background: "transparent",
							hoverBackground: "{primary.500}",
							hoverColor: "{surface.0}",
						},
						danger: {
							color: "{surface.0}",
							background: "{red.600}",
							hoverBackground: "{red.700}",
							hoverColor: "{surface.0}",
						},
					},
				},
				dark: {
					root: {
						primary: {
							color: "{surface.0}",
							background: "{primary.500}",
							hoverBackground: "{primary.600}",
							hoverColor: "{surface.0}",
							activeBackground: "{primary.700}",
							activeColor: "{surface.0}",
						},
						contrast: {
							// Primary with transparent background
							color: "{primary.500}",
							background: "transparent",
							hoverBackground: "{primary.500}",
							hoverColor: "{surface.0}",
							activeBackground: "{primary.500}",
							activeColor: "{surface.0}",
						},
						secondary: {
							// gray with transparent background
							color: "{text.mutedColor}",
							background: "transparent",
							hoverBackground: "color-mix(in srgb, {surface.700}, transparent 76%)",
							hoverColor: "{red.700}",
							activeBackground: "color-mix(in srgb, {surface.700}, transparent 76%)",
							activeColor: "{red.700}",
						},
						warn: {
							color: "{surface.100}",
							background: "{surface.700}",
							borderColor: "{surface.800}",
							hoverBackground: "{surface.600}",
							hoverColor: "{surface.0}",
							activeBackground: "{surface.600}",
							activeColor: "{surface.0}",
						},
						info: {
							color: "{text.mutedColor}",
							background: "transparent",
							hoverBackground: "color-mix(in srgb, {surface.700}, transparent 76%)",
							hoverColor: "{text.hoverMutedColor}",
							activeBackground: "color-mix(in srgb, {surface.700}, transparent 76%)",
							activeColor: "{text.hoverMutedColor}",
						},
						danger: {
							color: "{surface.0}",
							background: "{red.800}",
							hoverBackground: "{red.700}",
							hoverColor: "{surface.0}",
						},
					},
				},
			},
		},
		checkbox: {
			colorScheme: {
				light: {
					root: {
						disabledBackground: "{surface.100}",
						// disabledBorder: "{surface.100}",
						checkedDisabledBorderColor: "{surface.100}",
					},
					icon: {
						disabledColor: "{primary-500}",
					},
				},
				dark: {
					root: {
						background: "{surface.800}",
						borderColor: "{surface.700}",
						disabledBackground: "{surface.700}",
						checkedDisabledBorderColor: "{surface.700}",
					},
					icon: {
						disabledColor: "{primary-500}",
					},
				},
			},
		},
		divider: {
			horizontal: {
				margin: "0.25rem 0",
			},
			colorScheme: {
				light: {
					root: {
						borderColor: "{surface.300}",
					},
				},
				dark: {
					root: {
						borderColor: "{surface.900}",
					},
				},
			},
		},
		progressbar: {
			root: {
				height: "0.25rem",
			},
			colorScheme: {
				light: {
					root: {
						background: "{surface.300}",
					},
				},
				dark: {
					root: {
						background: "{surface.700}",
					},
				},
			},
		},
		progressspinner: {
			root: {
				colorOne: "{primary.400}",
				colorTwo: "{primary.500}",
				colorThree: "{primary.600}",
				colorFour: "{primary.700}",
			},
		},
		toggleswitch: {
			root: {
				width: "2rem",
				height: "1rem",
				gap: "0.25rem",
			},
			handle: {
				size: "0.75rem",
			},
			colorScheme: {
				dark: {
					root: {
						disabledBackground: "{surface.800}",
					},
					handle: {
						disabledBackground: "{surface.700}",
					},
				},
			},
		},
		drawer: {
			colorScheme: {
				light: {
					root: {
						// background: "{surface.0}",
						// color: "{surface.700}",
					},
				},
				dark: {
					root: {
						background: "{surface.900}",
						color: "{surface.200}",
						borderColor: "{surface.800}",
					},
				},
			},
		},
		toolbar: {
			root: {
				padding: "0 0.5rem",
			},
			colorScheme: {
				light: {
					root: {
						background: "{surface.50}",
					},
				},
				dark: {
					root: {
						// background: "linear-gradient(to bottom, {surface.800}, {surface.900});",
						background: "{surface.800}",
						color: "{surface.0}",
					},
				},
			},
		},
		dialog: {
			root: {
				borderColor: "transparent",
			},
			colorScheme: {
				light: {
					root: {},
				},
				dark: {
					root: {
						background: "{surface.800}",
						color: "{surface.0}",
					},
				},
			},
		},
		scrollpanel: {
			colorScheme: {
				light: {},
				dark: {
					bar: {
						background: "{surface.700}",
					},
				},
			},
		},
		panel: {
			colorScheme: {
				light: {
					root: {
						borderRadius: "0",
					},
					header: {
						color: "{surface.700}",
					},
				},
				dark: {
					root: {
						borderRadius: "0",
						background: "{surface.900}",
						color: "{surface.0}",
					},
					header: {
						color: "{surface.300}",
					},
				},
			},
		},
		timeline: {
			colorScheme: {
				dark: {
					eventMarker: {
						background: "{surface.800}",
						borderColor: "{surface.700}",
						content: {
							background: "{primary.600}",
						},
					},
					eventConnector: {
						color: "{surface.700}",
					},
				},
			},
		},
		paginator: {
			colorScheme: {
				light: {
					root: {},
					navButton: {
						hoverBackground: "color-mix(in srgb, {primary.50}, transparent 92%)",
						hoverColor: "{primary.500}",
						selectedBackground: "color-mix(in srgb, {primary.50}, transparent 84%)",
						selectedColor: "{primary.500}",
					},
				},
				dark: {
					root: {
						background: "transparent",
					},
					navButton: {
						hoverBackground: "color-mix(in srgb, {primary.50}, transparent 92%)",
						hoverColor: "{primary.500}",
						selectedBackground: "color-mix(in srgb, {primary.50}, transparent 84%)",
						selectedColor: "{primary.500}",
					},
				},
			},
		},
		datatable: {
			header: {
				background: "transparent",
			},
			row: {
				background: "transparent",
			},
			colorScheme: {
				light: {
					header: {
						color: "{surface.700}",
					},
					row: {
						color: "{surface.700}",
					},
					bodyCell: {
						borderColor: "{surface.300}",
					},
				},
				dark: {
					header: {
						color: "{surface.200}",
						borderColor: "{surface.700}",
					},

					headerCell: {
						borderColor: "{surface.700}",
						background: "{surface.800}",
						hoverBackground: "{surface.700}",
						color: "{surface.200}",
						hoverColor: "{surface.200}",
					},
					row: {
						color: "{surface.300}",
					},
					bodyCell: {
						borderColor: "{surface.700}",
					},
				},
			},
		},
		menu: {
			root: {
				borderRadius: "0",
				background: "transparent",
			},
			submenuLabel: {
				padding: "1rem 0.75rem 0.5rem 0.75rem",
			},
			colorScheme: {
				light: {
					root: {},
					item: {
						// color: "{surface.700}",
					},
					submenuLabel: {
						color: "color-mix(in srgb, var(--p-primary-hover-color) calc(100% * 1), transparent)",
					},
				},
				dark: {
					root: {
						borderRadius: "0",
						// color: "{surface.0}",
					},
					item: {
						color: "{surface.400}",
						focusBackground: "transparent",
						focusColor: "{primary.400}",
					},
					submenuLabel: {
						color: "color-mix(in srgb, var(--p-primary-hover-color) calc(100% * 1), transparent)",
					},
					separator: {
						borderColor: "{surface.700}",
					},
				},
			},
		},
		contextmenu: {
			colorScheme: {
				light: {
					// option: {
					// 	color: "{surface.500}",
					// 	focus: {
					// 		background: "linear-gradient({primary.500}, {primary.600})",
					// 		color: "{surface.0}",
					// 	},
					// },
				},
				dark: {
					root: {
						background: "{surface.800}",
						color: "{surface.200}",
						borderColor: "{surface.900}",
					},
					item: {
						color: "{surface.400}",
						padding: "0.1rem 0.75rem",
						focusBackground: "linear-gradient({primary.500}, {primary.600})",
						focusColor: "{surface.0}",
					},
					// option: {
					// 	color: "{surface.400}",
					// 	focus: {
					// 		background: "linear-gradient({primary.500}, {primary.600})",
					// 		color: "{surface.0}",
					// 	},
					// },
					// overlay: {
					// 	background: "{surface.900}",
					// 	border: {
					// 		color: "{surface.800}",
					// 	},
					// },
					// color: "{surface.300}",
				},
			},
		},
		fieldset: {
			colorScheme: {
				light: {},
				dark: {
					root: {
						borderColor: "{surface.700}",
						background: "{surface.900}",
						color: "{surface.0}",
					},
					legend: {
						background: "{surface.900}",
						hoverBackground: "{surface.800}",
						hoverColor: "{surface.0}",
					},
					toggleIcon: {
						hoverColor: "{primary.500}",
					},
				},
			},
		},
		togglebutton: {
			root: {
				padding: "0.15rem",
			},
			content: {
				padding: "0.1rem 0.75rem",
			},
		},
		card: {
			colorScheme: {
				light: {
					root: {
						borderRadius: "0",
						background: "{surface.0}",
						color: "{surface.700}",
						shadow: "none",
					},
				},
				dark: {
					root: {
						borderRadius: "0",
						background: "{surface.900}",
						color: "{surface.0}",
					},
					subtitle: {
						color: "{surface.400}",
					},
				},
			},
		},
		tabs: {
			colorScheme: {
				light: {
					tab: {
						background: "transparent",
						borderColor: "{surface.300}",
						hoverColor: "{primary.600}",
						hoverBorderColor: "{primary.600}",
					},
					tabpanel: {
						background: "transparent",
					},
					tablist: {
						background: "transparent",
						borderColor: "{surface.300}",
					},
				},
				dark: {
					tab: {
						background: "transparent",
						borderColor: "{surface.700}",
						hoverColor: "{primary.600}",
						hoverBorderColor: "{primary.600}",
					},
					tabpanel: {
						background: "transparent",
					},
					tablist: {
						background: "transparent",
						borderColor: "{surface.700}",
					},
				},
			},
		},
		stepper: {
			colorScheme: {
				light: {
					separator: {
						background: "{surface.500}",
						activeBackground: "{primary.500}",
					},
					stepNumber: {
						background: "transparent",
						activeBackground: "transparent",
						activeBorderColor: "{primary.500}",
						activeColor: "{primary.500}",
					},
					steppanel: {
						background: "transparent",
						color: "{surface.700}",
					},
					stepTitle: {
						color: "{surface.700}",
						activeColor: "{primary.500}",
					},
				},
				dark: {
					separator: {
						background: "{surface.400}",
						activeBackground: "{primary.400}",
					},
					stepTitle: {
						color: "{surface.200}",
						activeColor: "{primary.400}",
					},
					stepNumber: {
						background: "transparent",
						activeBackground: "transparent",
						activeBorderColor: "{primary.400}",
						activeColor: "{primary.400}",
					},
					steppanel: {
						background: "transparent",
						color: "{surface.300}",
					},
				},
			},
		},
		floatlabel: {
			on: {
				active: {
					background: "transparent",
				},
			},
		},
		inplace: {
			colorScheme: {
				light: {
					display: {
						hoverBackground: "transparent",
					},
				},
				dark: {
					display: {
						hoverBackground: "transparent",
					},
				},
			},
		},
		inputgroup: {
			addon: {
				background: "transparent",
			},
		},
		inputtext: {
			root: {
				paddingX: "1rem",
				paddingY: "0.25rem",
				borderRadius: "0",
				transitionDuration: "0",
				shadow: "none",
			},
			colorScheme: {
				light: {
					root: {
						color: "{surface.700}",
						background: "transparent",
						disabledBackground: "{surface.700}",
					},
				},
				dark: {
					root: {
						color: "{surface.300}",
						background: "transparent",
						disabledBackground: "{surface.950}",
					},
				},
			},
		},
		inputchips: {
			root: {
				background: "transparent",
				borderRadius: "0",
				borderColor: "transparent",
				transitionDuration: "0",
				shadow: "none",
			},
			colorScheme: {
				light: {
					root: {
						color: "{surface.700}",
					},
				},
				dark: {
					root: {
						color: "{surface.300}",
					},
				},
			},
		},
		datepicker: {
			inputIcon: { color: "{form.field.icon.color}" },
			group: { borderColor: "{content.border.color}", gap: "{overlay.popover.padding}" },
			dayView: { margin: "0.5rem 0 0 0" },
			weekDay: { padding: "0.25rem", fontWeight: "500", color: "{content.color}" },
			buttonbar: { padding: "0.5rem 0 0 0", borderColor: "{content.border.color}" },
			timePicker: { padding: "0.5rem 0 0 0", borderColor: "{content.border.color}", gap: "0.5rem", buttonGap: "0.25rem" },
			colorScheme: {
				light: {
					dropdown: {
						background: "{surface.100}",
						hoverBackground: "{surface.200}",
						activeBackground: "{surface.300}",
						color: "{surface.600}",
						hoverColor: "{surface.700}",
						activeColor: "{surface.800}",
					},
					today: { background: "{surface.200}", color: "{surface.900}" },
				},
				dark: {
					dropdown: {
						background: "{surface.800}",
						hoverBackground: "{surface.700}",
						activeBackground: "{surface.600}",
						color: "{surface.300}",
						hoverColor: "{surface.200}",
						activeColor: "{surface.100}",
					},
					header: {
						background: "{content.background}",
						borderColor: "{content.border.color}",
						color: "{content.color}",
						padding: "0 0 0.5rem 0",
					},
					title: {
						fontWeight: "500",
						gap: "0.5rem",
					},
					panel: {
						background: "{content.background}",
						borderColor: "{content.border.color}",
						color: "{content.color}",
						borderRadius: "{content.border.radius}",
						shadow: "{overlay.popover.shadow}",
						padding: "{overlay.popover.padding}",
					},
					selectMonth: {
						hoverBackground: "{content.hover.background}",
						color: "{content.color}",
						hoverColor: "{content.hover.color}",
						padding: "0.25rem 0.5rem",
						borderRadius: "{content.border.radius}",
					},
					selectYear: {
						hoverBackground: "{content.hover.background}",
						color: "{content.color}",
						hoverColor: "{content.hover.color}",
						padding: "0.25rem 0.5rem",
						borderRadius: "{content.border.radius}",
					},
					date: {
						hoverBackground: "{content.hover.background}",
						selectedBackground: "{primary.color}",
						rangeSelectedBackground: "{highlight.background}",
						color: "{content.color}",
						hoverColor: "{content.hover.color}",
						selectedColor: "{primary.contrast.color}",
						rangeSelectedColor: "{highlight.color}",
						width: "2rem",
						height: "2rem",
						borderRadius: "50%",
						padding: "0.25rem",
						focusRing: {
							width: "{focus.ring.width}",
							style: "{focus.ring.style}",
							color: "{focus.ring.color}",
							offset: "{focus.ring.offset}",
							shadow: "{focus.ring.shadow}",
						},
					},
					monthView: { margin: "0.5rem 0 0 0" },
					month: { borderRadius: "{content.border.radius}" },
					yearView: { margin: "0.5rem 0 0 0" },
					year: { borderRadius: "{content.border.radius}" },
					group: { borderColor: "{content.border.color}", gap: "{overlay.popover.padding}" },
					dayView: { margin: "0.5rem 0 0 0" },
					weekDay: { padding: "0.25rem", fontWeight: "500", color: "{content.color}" },
					today: { background: "{surface.700}", color: "{surface.0}" },
					timePicker: { padding: "0.5rem 0 0 0", borderColor: "{content.border.color}", gap: "0.5rem", buttonGap: "0.25rem" },
				},
			},
		},
		select: {
			root: {
				background: "transparent",
				borderRadius: "0",
				paddingX: "1rem",
				paddingY: "0.25rem",
				transitionDuration: "0",
				shadow: "none",
			},
			option: {
				padding: "0 1rem",
				borderRadius: "0",
			},
			colorScheme: {
				light: {
					option: {
						color: "{surface.500}",
						focusBackground: "linear-gradient({primary.500}, {primary.600})",
						focusColor: "{surface.0}",
					},
				},
				dark: {
					root: {
						disabledBackground: "transparent",
						color: "{surface.300}",
					},
					option: {
						color: "{surface.400}",
						focusBackground: "linear-gradient({primary.500}, {primary.600})",
						focusColor: "{surface.0}",
					},
					overlay: {
						background: "{surface.900}",
						borderColor: "{surface.800}",
					},
				},
			},
		},
		textarea: {
			root: {
				background: "transparent",
				borderRadius: "0",
				transitionDuration: "0",
				shadow: "none",
			},
			colorScheme: {
				light: {
					root: {
						color: "{surface.700}",
					},
				},
				dark: {
					root: {
						color: "{surface.300}",
					},
				},
			},
		},
		tooltip: {
			root: {
				maxWidth: "20rem",
			},
		},
		listbox: {
			root: {
				background: "transparent",
			},
			option: {
				padding: "0 0 0 1rem",
			},
			checkmark: {
				color: "{primary.400}",
			},
			colorScheme: {
				dark: {
					root: {
						borderColor: "{surface.700}",
					},
					option: {
						color: "{text.hoverMutedColor}",
						focusBackground: "{highlight.background}",
						focusColor: "{content.color}",
					},
				},
			},
		},
	},
};

export default LycheePrimeVueConfig;
