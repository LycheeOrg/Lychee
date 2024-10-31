interface ImportMetaEnv {
	readonly VITE_LOCAL_DEV: string;
	readonly MODE: "development" | "production";
}

interface ImportMeta {
	readonly env: ImportMetaEnv;
}
