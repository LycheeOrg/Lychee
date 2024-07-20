import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const UsersService = {
	count(): Promise<AxiosResponse<number>> {
		return axios.get(`${Constants.API_URL}Users::count`, { data: {} });
	},
};

export default UsersService;
