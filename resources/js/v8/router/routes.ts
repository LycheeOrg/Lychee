import { paths } from "@/router/paths";

const Placeholder = () => import("@/v8/views/Placeholder.vue");

const Landing = () => import("@/v8/views/Landing.vue");
const Home = () => import("@/v8/views/Home.vue");
const Album = () => import("@/v8/views/gallery-panels/Album.vue");
const Albums = () => import("@/v8/views/gallery-panels/Albums.vue");
const RenamerRules = () => import("@/v8/views/RenamerRules.vue");
const Tag = () => import("@/v8/views/gallery-panels/Tag.vue");
const Timeline = () => import("@/v8/views/gallery-panels/Timeline.vue");
const MapView = () => import("@/v8/views/gallery-panels/Map.vue");
const Diagnostics = () => import("@/v8/views/Diagnostics.vue");
const FaceMaintenance = () => import("@/v8/views/face-recog/FaceMaintenance.vue");
const NsfwConfig = () => import("@/v8/views/admin/NsfwConfig.vue");
const WatermarkPreview = () => import("@/v8/views/admin/WatermarkPreview.vue");
const Settings = () => import("@/v8/views/admin/Settings.vue");
const Sharing = () => import("@/v8/views/Sharing.vue");
const Users = () => import("@/v8/views/admin/Users.vue");
const UserGroups = () => import("@/v8/views/admin/UserGroups.vue");
const RegisterPage = () => import("@/v8/views/RegisterPage.vue");
const ContactMessages = () => import("@/v8/views/admin/ContactMessages.vue");
const Webhooks = () => import("@/v8/views/admin/Webhooks.vue");
const BulkAlbumEdit = () => import("@/v8/views/BulkAlbumEdit.vue");
const Moderation = () => import("@/v8/views/admin/Moderation.vue");
const AdminDashboard = () => import("@/v8/views/admin/AdminDashboard.vue");
const CheckoutPage = () => import("@/v8/views/webshop/CheckoutPage.vue");
const OrderList = () => import("@/v8/views/webshop/OrderList.vue");
const OrderDownload = () => import("@/v8/views/webshop/OrderDownload.vue");
const People = () => import("@/v8/views/face-recog/People.vue");
const PersonDetail = () => import("@/v8/views/face-recog/PersonDetail.vue");
const FaceClusters = () => import("@/v8/views/face-recog/FaceClusters.vue");
const Permissions = () => import("@/v8/views/Permissions.vue");
const Profile = () => import("@/v8/views/Profile.vue");
const TagsManagement = () => import("@/v8/views/TagsManagement.vue");
const Contact = () => import("@/v8/views/Contact.vue");
const ChangeLogs = () => import("@/v8/views/ChangeLogs.vue");
const Statistics = () => import("@/v8/views/Statistics.vue");
const Favourites = () => import("@/v8/views/gallery-panels/Favourites.vue");
const Flow = () => import("@/v8/views/gallery-panels/Flow.vue");
const Frame = () => import("@/v8/views/gallery-panels/Frame.vue");
const Search = () => import("@/v8/views/gallery-panels/Search.vue");
const LoginPage = () => import("@/v8/views/LoginPage.vue");
const BasketList = () => import("@/v8/views/webshop/BasketList.vue");
const Purchasables = () => import("@/v8/views/admin/Purchasables.vue");
const Jobs = () => import("@/v8/views/admin/Jobs.vue");
const Maintenance = () => import("@/v8/views/admin/Maintenance.vue");
const PrintPixelSizesAdmin = () => import("@/v8/views/admin/shop/PrintPixelSizesAdmin.vue");
const FixTree = () => import("@/v8/views/FixTree.vue");
const DuplicatesFinder = () => import("@/v8/views/DuplicatesFinder.vue");

/**
 * v8 (Nuxt UI) route table. Consumes the same shared `paths` manifest as the
 * v7 router (`@/router/routes.ts`) so both bundles serve identical URLs (see
 * Feature 049 / ADR-0006). Routes not yet migrated fall back to Placeholder;
 * each build-out task replaces its own entries here as it lands.
 */
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const componentByName: Record<string, any> = {
	landing: Landing,
	home: Home,
	album: Album,
	"flow-album": Album,
	gallery: Albums,
	"renamer-rules": RenamerRules,
	tag: Tag,
	timeline: Timeline,
	map: MapView,
	diagnostics: Diagnostics,
	"face-maintenance": FaceMaintenance,
	"nsfw-config": NsfwConfig,
	"watermark-preview": WatermarkPreview,
	settings: Settings,
	sharing: Sharing,
	users: Users,
	"user-groups": UserGroups,
	register: RegisterPage,
	"contact-messages": ContactMessages,
	webhooks: Webhooks,
	"bulk-album-edit": BulkAlbumEdit,
	moderation: Moderation,
	"admin-dashboard": AdminDashboard,
	checkout: CheckoutPage,
	orders: OrderList,
	order: OrderDownload,
	people: People,
	"face-clusters": FaceClusters,
	person: PersonDetail,
	permissions: Permissions,
	profile: Profile,
	tags: TagsManagement,
	contact: Contact,
	changelogs: ChangeLogs,
	statistics: Statistics,
	favourites: Favourites,
	flow: Flow,
	frame: Frame,
	search: Search,
	login: LoginPage,
	basket: BasketList,
	purchasables: Purchasables,
	jobs: Jobs,
	maintenance: Maintenance,
	"shop-sizes": PrintPixelSizesAdmin,
	tree: FixTree,
	duplicates: DuplicatesFinder,
};

const routes_ = paths.map((p) => ({
	name: p.name,
	path: p.path,
	component: componentByName[p.name] ?? Placeholder,
	...(p.props ? { props: true } : {}),
}));

if (import.meta.env.MODE === "development" && import.meta.env.VITE_LOCAL_DEV === "true") {
	routes_.push({
		name: "local-dev",
		path: "/vite/index.html",
		component: Placeholder,
	});
}

export const routes = routes_;
