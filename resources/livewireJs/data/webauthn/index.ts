import { loginWebAuthn } from "./loginWebAuthn";
import { registerWebAuthn } from "./registerWebAuthn";

export const webauthn = {
	loginWebAuthn,
	registerWebAuthn,
	[Symbol.iterator]: function* () {
		yield* Object.values(this);
	},
};
