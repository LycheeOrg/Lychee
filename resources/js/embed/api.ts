import type { EmbedApiResponse } from "./types";

/**
 * API client for fetching album data from Lychee
 */
export class EmbedApiClient {
	private apiUrl: string;

	constructor(apiUrl: string) {
		this.apiUrl = apiUrl;
	}

	/**
	 * Fetch album data for embedding
	 *
	 * @param albumId Album ID to fetch
	 * @returns Album and photos data
	 * @throws Error if fetch fails or album is not accessible
	 */
	async fetchAlbum(albumId: string): Promise<EmbedApiResponse> {
		const url = `${this.apiUrl}/api/Embed/${encodeURIComponent(albumId)}`;

		try {
			const response = await fetch(url, {
				method: "GET",
				headers: {
					Accept: "application/json",
					"Content-Type": "application/json",
				},
				mode: "cors",
				credentials: "omit", // Don't send cookies for embed requests
			});

			if (!response.ok) {
				await this.handleErrorResponse(response);
			}

			const data = await response.json();
			return this.validateApiResponse(data);
		} catch (error) {
			if (error instanceof Error) {
				throw new Error(`Failed to fetch album: ${error.message}`);
			}
			throw new Error("Failed to fetch album: Unknown error");
		}
	}

	/**
	 * Handle HTTP error responses
	 *
	 * @param response HTTP response object
	 * @throws Error with appropriate message based on status code
	 */
	private async handleErrorResponse(response: Response): Promise<never> {
		const status = response.status;

		switch (status) {
			case 404:
				throw new Error("Album not found. Please check the album ID.");
			case 403:
				throw new Error("Album is not publicly accessible. Only public albums without password protection can be embedded.");
			case 500:
				throw new Error("Server error. Please try again later.");
			case 503:
				throw new Error("Service unavailable. Please try again later.");
			default:
				throw new Error(`HTTP ${status}: Failed to fetch album data.`);
		}
	}

	/**
	 * Validate API response structure
	 *
	 * @param data Response data to validate
	 * @returns Validated API response
	 * @throws Error if response structure is invalid
	 */
	private validateApiResponse(data: unknown): EmbedApiResponse {
		if (!data || typeof data !== "object") {
			throw new Error("Invalid API response: Expected object");
		}

		const response = data as Record<string, unknown>;

		// Validate album object
		if (!response.album || typeof response.album !== "object") {
			throw new Error("Invalid API response: Missing or invalid album data");
		}

		const album = response.album as Record<string, unknown>;
		if (!album.id || typeof album.id !== "string") {
			throw new Error("Invalid API response: Album missing ID");
		}

		// Validate photos array
		if (!Array.isArray(response.photos)) {
			throw new Error("Invalid API response: Photos must be an array");
		}

		// Basic validation passed, return as typed response
		return data as EmbedApiResponse;
	}
}

/**
 * Create a new API client instance
 *
 * @param apiUrl Base URL of the Lychee API
 * @returns API client instance
 */
export function createApiClient(apiUrl: string): EmbedApiClient {
	return new EmbedApiClient(apiUrl);
}
