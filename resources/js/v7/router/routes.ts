import Album from "@/v7/views/gallery-panels/Album.vue";
import Albums from "@/v7/views/gallery-panels/Albums.vue";
import { paths } from "@/router/paths";

const Landing = () => import("@/v7/views/Landing.vue");
const Favourites = () => import("@/v7/views/gallery-panels/Favourites.vue");
const Home = () => import("@/v7/views/Home.vue");
const Timeline = () => import("@/v7/views/gallery-panels/Timeline.vue");
const Frame = () => import("@/v7/views/gallery-panels/Frame.vue");
const Search = () => import("@/v7/views/gallery-panels/Search.vue");
const MapView = () => import("@/v7/views/gallery-panels/Map.vue");
const Permissions = () => import("@/v7/views/Permissions.vue");
const Users = () => import("@/v7/views/admin/Users.vue");
const Sharing = () => import("@/v7/views/Sharing.vue");
const Settings = () => import("@/v7/views/admin/Settings.vue");
const Profile = () => import("@/v7/views/Profile.vue");
const Maintenance = () => import("@/v7/views/admin/Maintenance.vue");
const Diagnostics = () => import("@/v7/views/Diagnostics.vue");
const Statistics = () => import("@/v7/views/Statistics.vue");
const Jobs = () => import("@/v7/views/admin/Jobs.vue");
const FixTree = () => import("@/v7/views/FixTree.vue");
const DuplicatesFinder = () => import("@/v7/views/DuplicatesFinder.vue");
const Changelogs = () => import("@/v7/views/ChangeLogs.vue");
const LoginPage = () => import("@/v7/views/LoginPage.vue");
const UserGroups = () => import("@/v7/views/admin/UserGroups.vue");
const RegisterPage = () => import("@/v7/views/RegisterPage.vue");
const Flow = () => import("@/v7/views/gallery-panels/Flow.vue");
const TagsManagement = () => import("@/v7/views/TagsManagement.vue");
const Tag = () => import("@/v7/views/gallery-panels/Tag.vue");
const RenamerRules = () => import("@/v7/views/RenamerRules.vue");
const Purchasables = () => import("@/v7/views/admin/Purchasables.vue");
const PrintPixelSizesAdmin = () => import("@/v7/views/admin/shop/PrintPixelSizesAdmin.vue");
const Contact = () => import("@/v7/views/Contact.vue");
const ContactMessages = () => import("@/v7/views/admin/ContactMessages.vue");
const Webhooks = () => import("@/v7/views/admin/Webhooks.vue");
const BulkAlbumEdit = () => import("@/v7/views/BulkAlbumEdit.vue");
const Moderation = () => import("@/v7/views/admin/Moderation.vue");
const AdminDashboard = () => import("@/v7/views/admin/AdminDashboard.vue");
const BasketList = () => import("@/v7/views/webshop/BasketList.vue");
const CheckoutPage = () => import("@/v7/views/webshop/CheckoutPage.vue");
const OrderList = () => import("@/v7/views/webshop/OrderList.vue");
const OrderDownload = () => import("@/v7/views/webshop/OrderDownload.vue");
const People = () => import("@/v7/views/face-recog/People.vue");
const PersonDetail = () => import("@/v7/views/face-recog/PersonDetail.vue");
const FaceClusters = () => import("@/v7/views/face-recog/FaceClusters.vue");
const FaceMaintenance = () => import("@/v7/views/face-recog/FaceMaintenance.vue");
const NsfwConfig = () => import("@/v7/views/admin/NsfwConfig.vue");

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
