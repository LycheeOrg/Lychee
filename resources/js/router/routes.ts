import Album from "@/views/gallery-panels/Album.vue";
import Albums from "@/views/gallery-panels/Albums.vue";
import { paths } from "@/router/paths";

const Landing = () => import("@/views/Landing.vue");
const Favourites = () => import("@/views/gallery-panels/Favourites.vue");
const Home = () => import("@/views/Home.vue");
const Timeline = () => import("@/views/gallery-panels/Timeline.vue");
const Frame = () => import("@/views/gallery-panels/Frame.vue");
const Search = () => import("@/views/gallery-panels/Search.vue");
const MapView = () => import("@/views/gallery-panels/Map.vue");
const Permissions = () => import("@/views/Permissions.vue");
const Users = () => import("@/views/admin/Users.vue");
const Sharing = () => import("@/views/Sharing.vue");
const Settings = () => import("@/views/admin/Settings.vue");
const Profile = () => import("@/views/Profile.vue");
const Maintenance = () => import("@/views/admin/Maintenance.vue");
const Diagnostics = () => import("@/views/Diagnostics.vue");
const Statistics = () => import("@/views/Statistics.vue");
const Jobs = () => import("@/views/admin/Jobs.vue");
const FixTree = () => import("@/views/FixTree.vue");
const DuplicatesFinder = () => import("@/views/DuplicatesFinder.vue");
const Changelogs = () => import("@/views/ChangeLogs.vue");
const LoginPage = () => import("@/views/LoginPage.vue");
const UserGroups = () => import("@/views/admin/UserGroups.vue");
const RegisterPage = () => import("@/views/RegisterPage.vue");
const Flow = () => import("@/views/gallery-panels/Flow.vue");
const TagsManagement = () => import("@/views/TagsManagement.vue");
const Tag = () => import("@/views/gallery-panels/Tag.vue");
const RenamerRules = () => import("@/views/RenamerRules.vue");
const Purchasables = () => import("@/views/admin/Purchasables.vue");
const PrintPixelSizesAdmin = () => import("@/views/admin/shop/PrintPixelSizesAdmin.vue");
const Contact = () => import("@/views/Contact.vue");
const ContactMessages = () => import("@/views/admin/ContactMessages.vue");
const Webhooks = () => import("@/views/admin/Webhooks.vue");
const BulkAlbumEdit = () => import("@/views/BulkAlbumEdit.vue");
const Moderation = () => import("@/views/admin/Moderation.vue");
const AdminDashboard = () => import("@/views/admin/AdminDashboard.vue");
const BasketList = () => import("@/views/webshop/BasketList.vue");
const CheckoutPage = () => import("@/views/webshop/CheckoutPage.vue");
const OrderList = () => import("@/views/webshop/OrderList.vue");
const OrderDownload = () => import("@/views/webshop/OrderDownload.vue");
const People = () => import("@/views/face-recog/People.vue");
const PersonDetail = () => import("@/views/face-recog/PersonDetail.vue");
const FaceClusters = () => import("@/views/face-recog/FaceClusters.vue");
const FaceMaintenance = () => import("@/views/face-recog/FaceMaintenance.vue");
const NsfwConfig = () => import("@/views/admin/NsfwConfig.vue");

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const componentByName: Record<string, any> = {
	landing: Landing,
	favourites: Favourites,
	album: Album,
	home: Home,
	flow: Flow,
	tags: TagsManagement,
	"renamer-rules": RenamerRules,
	tag: Tag,
	"flow-album": Album,
	gallery: Albums,
	frame: Frame,
	timeline: Timeline,
	map: MapView,
	search: Search,
	diagnostics: Diagnostics,
	permissions: Permissions,
	jobs: Jobs,
	maintenance: Maintenance,
	"face-maintenance": FaceMaintenance,
	"nsfw-config": NsfwConfig,
	tree: FixTree,
	duplicates: DuplicatesFinder,
	profile: Profile,
	settings: Settings,
	sharing: Sharing,
	statistics: Statistics,
	users: Users,
	changelogs: Changelogs,
	login: LoginPage,
	"user-groups": UserGroups,
	register: RegisterPage,
	contact: Contact,
	"contact-messages": ContactMessages,
	webhooks: Webhooks,
	"bulk-album-edit": BulkAlbumEdit,
	moderation: Moderation,
	purchasables: Purchasables,
	"shop-sizes": PrintPixelSizesAdmin,
	"admin-dashboard": AdminDashboard,
	basket: BasketList,
	checkout: CheckoutPage,
	orders: OrderList,
	order: OrderDownload,
	people: People,
	"face-clusters": FaceClusters,
	person: PersonDetail,
};

const routes_ = paths.map((p) => ({
	name: p.name,
	path: p.path,
	component: componentByName[p.name],
	...(p.props ? { props: true } : {}),
}));

if (import.meta.env.MODE === "development" && import.meta.env.VITE_LOCAL_DEV === "true") {
	routes_.push({
		name: "local-dev",
		path: "/vite/index.html",
		component: Landing,
	});
}

export const routes = routes_;
