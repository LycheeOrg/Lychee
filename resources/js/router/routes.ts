import Album from "@/views/gallery-panels/Album.vue";
import Albums from "@/views/gallery-panels/Albums.vue";

const Landing = () => import("@/views/Landing.vue");
const Favourites = () => import("@/views/gallery-panels/Favourites.vue");
const Home = () => import("@/views/Home.vue");
const Frame = () => import("@/views/gallery-panels/Frame.vue");
const Search = () => import("@/views/gallery-panels/Search.vue");
const MapView = () => import("@/views/gallery-panels/Map.vue");
const Permissions = () => import("@/views/Permissions.vue");
const Users = () => import("@/views/Users.vue");
const Sharing = () => import("@/views/Sharing.vue");
const Settings = () => import("@/views/Settings.vue");
const Profile = () => import("@/views/Profile.vue");
const Maintenance = () => import("@/views/Maintenance.vue");
const Diagnostics = () => import("@/views/Diagnostics.vue");
const Statistics = () => import("@/views/Statistics.vue");
const Jobs = () => import("@/views/Jobs.vue");
const FixTree = () => import("@/views/FixTree.vue");
const DuplicatesFinder = () => import("@/views/DuplicatesFinder.vue");
const Changelogs = () => import("@/views/ChangeLogs.vue");
const LoginPage = () => import("@/views/LoginPage.vue");
const UserGroups = () => import("@/views/UserGroups.vue");
const RegisterPage = () => import("@/views/RegisterPage.vue");
const Flow = () => import("@/views/gallery-panels/Flow.vue");
const TagsManagement = () => import("@/views/TagsManagement.vue");
const Tag = () => import("@/views/gallery-panels/Tag.vue");
const RenamerRules = () => import("@/views/RenamerRules.vue");

const routes_ = [
	{
		name: "landing",
		path: "/",
		component: Landing,
	},
	{
		name: "favourites",
		path: "/gallery/favourites",
		component: Favourites,
	},
	{
		name: "album",
		path: "/gallery/:albumId/:photoId?",
		component: Album,
		props: true,
	},
	{
		name: "home",
		path: "/home",
		component: Home,
	},
	{
		name: "flow",
		path: "/flow",
		component: Flow,
	},
	{
		name: "tags",
		path: "/tags",
		component: TagsManagement,
	},
	{
		name: "renamer-rules",
		path: "/renamerRules",
		component: RenamerRules,
	},
	{
		name: "tag",
		path: "/tag/:tagId/:photoId?",
		component: Tag,
		props: true,
	},
	{
		name: "flow-album",
		path: "/flow/:albumId/:photoId?",
		component: Album,
		props: true,
	},
	{
		name: "gallery",
		path: "/gallery",
		component: Albums,
	},
	{
		name: "frame",
		path: "/frame/:albumId?",
		component: Frame,
		props: true,
	},
	{
		name: "map",
		path: "/map/:albumId?",
		component: MapView,
		props: true,
	},
	{
		name: "search",
		path: "/search/:albumId?/:photoId?",
		component: Search,
		props: true,
	},
	{
		name: "diagnostics",
		path: "/diagnostics",
		component: Diagnostics,
	},
	{
		name: "permissions",
		path: "/permissions",
		component: Permissions,
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
		name: "tree",
		path: "/fixTree",
		component: FixTree,
	},
	{
		name: "duplicates",
		path: "/duplicatesFinder",
		component: DuplicatesFinder,
	},
	{
		name: "profile",
		path: "/profile",
		component: Profile,
	},
	{
		name: "settings",
		path: "/settings/:tab?",
		component: Settings,
		props: true,
	},
	{
		name: "sharing",
		path: "/sharing",
		component: Sharing,
	},
	{
		name: "statistics",
		path: "/statistics",
		component: Statistics,
	},
	{
		name: "users",
		path: "/users",
		component: Users,
	},
	{
		name: "changelogs",
		path: "/changelogs",
		component: Changelogs,
	},
	{
		name: "login",
		path: "/login",
		component: LoginPage,
	},
	{
		name: "user-groups",
		path: "/user-groups",
		component: UserGroups,
	},
	{
		name: "register",
		path: "/register",
		component: RegisterPage,
	},
];

if (import.meta.env.MODE === "development" && import.meta.env.VITE_LOCAL_DEV === "true") {
	routes_.push({
		name: "local-dev",
		path: "/vite/index.html",
		component: Landing,
	});
}

export const routes = routes_;
