/**
 * Shared, component-free route manifest.
 *
 * This is the single source of truth for the app's route names/paths. Both
 * the v7 router (`@/router/routes.ts`, PrimeVue) and the v8 router
 * (`@/v8/router/routes.ts`, Nuxt UI) attach components to this same list, so
 * both bundles are reachable at identical URLs regardless of which is served
 * (see Feature 049 / ADR-0006).
 */
export interface RoutePath {
	name: string;
	path: string;
	props?: boolean;
}

export const paths: RoutePath[] = [
	{
		name: "landing",
		path: "/",
	},
	{
		name: "favourites",
		path: "/gallery/favourites",
	},
	{
		name: "album",
		path: "/gallery/:albumId/:photoId?",
		props: true,
	},
	{
		name: "home",
		path: "/home",
	},
	{
		name: "flow",
		path: "/flow",
	},
	{
		name: "tags",
		path: "/tags",
	},
	{
		name: "renamer-rules",
		path: "/renamerRules",
	},
	{
		name: "tag",
		path: "/tag/:tagId/:photoId?",
		props: true,
	},
	{
		name: "flow-album",
		path: "/flow/:albumId/:photoId?",
		props: true,
	},
	{
		name: "gallery",
		path: "/gallery",
	},
	{
		name: "frame",
		path: "/frame/:albumId?",
		props: true,
	},
	{
		name: "timeline",
		path: "/timeline/:date?/:photoId?",
		props: true,
	},
	{
		name: "map",
		path: "/map/:albumId?",
		props: true,
	},
	{
		name: "search",
		path: "/search/:albumId?/:photoId?",
		props: true,
	},
	{
		name: "diagnostics",
		path: "/diagnostics",
	},
	{
		name: "permissions",
		path: "/permissions",
	},
	{
		name: "jobs",
		path: "/admin/jobs",
	},
	{
		name: "maintenance",
		path: "/admin/maintenance",
	},
	{
		name: "face-maintenance",
		path: "/admin/maintenance/faces",
	},
	{
		name: "nsfw-config",
		path: "/admin/nsfw-config",
	},
	{
		name: "tree",
		path: "/fixTree",
	},
	{
		name: "duplicates",
		path: "/duplicatesFinder",
	},
	{
		name: "profile",
		path: "/profile",
	},
	{
		name: "settings",
		path: "/admin/settings/:tab?",
		props: true,
	},
	{
		name: "sharing",
		path: "/sharing",
	},
	{
		name: "statistics",
		path: "/statistics",
	},
	{
		name: "users",
		path: "/admin/users",
	},
	{
		name: "changelogs",
		path: "/changelogs",
	},
	{
		name: "login",
		path: "/login",
	},
	{
		name: "user-groups",
		path: "/admin/user-groups",
	},
	{
		name: "register",
		path: "/register",
	},
	{
		name: "contact",
		path: "/contact",
	},
	{
		name: "contact-messages",
		path: "/admin/contact-messages",
	},
	{
		name: "webhooks",
		path: "/admin/webhooks",
	},
	{
		name: "bulk-album-edit",
		path: "/bulk-album-edit",
	},
	{
		name: "moderation",
		path: "/admin/moderation",
	},
	{
		name: "purchasables",
		path: "/admin/purchasables",
	},
	{
		name: "shop-sizes",
		path: "/admin/shop/sizes",
	},
	{
		name: "admin-dashboard",
		path: "/admin",
	},
	{
		name: "basket",
		path: "/basket",
	},
	{
		name: "checkout",
		path: "/checkout/:step?",
		props: true,
	},
	{
		name: "orders",
		path: "/orders",
	},
	{
		name: "order",
		path: "/order/:orderId?/:transactionId?",
		props: true,
	},
	{
		name: "people",
		path: "/people",
	},
	{
		name: "face-clusters",
		path: "/people/clusters",
	},
	{
		name: "person",
		path: "/people/:personId/:photoId?",
		props: true,
	},
];
