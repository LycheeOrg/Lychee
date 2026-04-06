import Constants from "./constants";
import axios from "axios";

/**
 * Batch face assignment/unassignment operations.
 */
export default class FaceBatchService {
	/**
	 * Batch assign multiple faces to an existing person or create a new person.
	 *
	 * @param faceIds Array of face IDs to assign
	 * @param personId Optional existing person ID
	 * @param newPersonName Optional new person name (when creating a new person)
	 * @returns Response with affected count and person ID
	 */
	public static async batchAssign(
		faceIds: string[],
		options: { personId?: string; newPersonName?: string },
	): Promise<{ affected_count: number; person_id: string | null }> {
		const payload: {
			face_ids: string[];
			action: string;
			person_id?: string;
			new_person_name?: string;
		} = {
			face_ids: faceIds,
			action: "assign",
		};

		if (options.personId) {
			payload.person_id = options.personId;
		} else if (options.newPersonName) {
			payload.new_person_name = options.newPersonName;
		}

		const response = await axios.post(`${Constants.getApiUrl()}Face/batch`, payload);
		return response.data;
	}

	/**
	 * Batch unassign multiple faces (remove person assignment).
	 *
	 * @param faceIds Array of face IDs to unassign
	 * @returns Response with affected count
	 */
	public static async batchUnassign(faceIds: string[]): Promise<{ affected_count: number; person_id: null }> {
		const response = await axios.post(`${Constants.getApiUrl()}Face/batch`, {
			face_ids: faceIds,
			action: "unassign",
		});
		return response.data;
	}
}
