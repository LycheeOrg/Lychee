/**
 * WebGL2 program of the 360° sphere view (Feature 081, FR-081-12, FR-081-14).
 *
 * One full-screen triangle; the fragment shader turns each pixel into a view
 * ray (camera space: x right, y up, z forward), rotates it into sphere space
 * (`u_rotation`, see `rotationMatrix()` in `sphere.ts`), converts it to
 * longitude/latitude and samples the equirectangular texture. Pixels outside
 * the covered area of a partial panorama get the background colour.
 *
 * Texture gradients are computed from a longitude that is continuous where the
 * view crosses ±180°, and passed to `textureGrad`, so the mipmap level does not
 * jump at the seam.
 */

export const VERTEX_SHADER = `#version 300 es
out vec2 v_ndc;

void main() {
	vec2 corner = vec2(float((gl_VertexID << 1) & 2), float(gl_VertexID & 2));
	v_ndc = corner * 2.0 - 1.0;
	gl_Position = vec4(v_ndc, 0.0, 1.0);
}
`;

export const FRAGMENT_SHADER = `#version 300 es
precision highp float;

in vec2 v_ndc;

uniform sampler2D u_texture;
uniform mat3 u_rotation;
// tan(horizontal fov / 2), tan(vertical fov / 2)
uniform vec2 u_tan_half;
// longitude min, longitude span, latitude max, latitude span (radians)
uniform vec4 u_coverage;
uniform vec3 u_background;

out vec4 out_colour;

void main() {
	vec3 ray = normalize(u_rotation * vec3(v_ndc.x * u_tan_half.x, v_ndc.y * u_tan_half.y, 1.0));
	float lon = atan(ray.x, ray.z);
	float lat = asin(clamp(ray.y, -1.0, 1.0));

	// Same longitude shifted by 180°: continuous where lon jumps from +PI to -PI.
	float lon_shifted = atan(-ray.x, -ray.z);
	vec2 d_lon = vec2(dFdx(lon), dFdy(lon));
	vec2 d_lon_shifted = vec2(dFdx(lon_shifted), dFdy(lon_shifted));
	if (dot(d_lon_shifted, d_lon_shifted) < dot(d_lon, d_lon)) {
		d_lon = d_lon_shifted;
	}
	vec2 d_lat = vec2(dFdx(lat), dFdy(lat));

	vec2 uv = vec2((lon - u_coverage.x) / u_coverage.y, (u_coverage.z - lat) / u_coverage.w);
	vec2 du = d_lon / u_coverage.y;
	vec2 dv = -d_lat / u_coverage.w;

	if (uv.x < 0.0 || uv.x > 1.0 || uv.y < 0.0 || uv.y > 1.0) {
		out_colour = vec4(u_background, 1.0);
		return;
	}
	out_colour = textureGrad(u_texture, uv, vec2(du.x, dv.x), vec2(du.y, dv.y));
}
`;
