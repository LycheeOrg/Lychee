import Diagnostics from "@/views/Diagnostics.vue";
import Jobs from "@/views/Jobs.vue";
import Landing from "@/views/Landing.vue";
import Maintenance from "@/views/Maintenance.vue";
import Profile from "@/views/Profile.vue";
import Settings from "@/views/Settings.vue";
import Sharing from "@/views/Sharing.vue";
import Users from "@/views/Users.vue";
import Album from "@/views/gallery-panels/Album.vue";
import Albums from "@/views/gallery-panels/Albums.vue";
import Photo from "@/views/gallery-panels/Photo.vue";
import Search from "@/views/gallery-panels/Search.vue";

export const routes = [
	{
		name: "landing",
		path: "/",
		component: Landing,
	},
	{
		name: "photo",
		path: "/gallery/:albumid/:photoid",
		component: Photo,
		props: true,
	},
	{
		name: "album",
		path: "/gallery/:albumid",
		component: Album,
		props: true,
	},
	{
		name: "gallery",
		path: "/gallery",
		component: Albums,
	},
	{
		name: "search",
		path: "/search",
		component: Search,
	},
	{
		name: "diagnostics",
		path: "/diagnostics",
		component: Diagnostics,
	},
	{
		name: "jobs",
		path: "/jobs",
		component: Jobs,
	},
	{
		name: "maintenance",
		path: "/maintenance",
		component: Maintenance,
	},
	{
		name: "profile",
		path: "/profile",
		component: Profile,
	},
	{
		name: "settings",
		path: "/settings",
		component: Settings,
	},
	{
		name: "sharing",
		path: "/sharing",
		component: Sharing,
	},
	{
		name: "users",
		path: "/users",
		component: Users,
	},
];
