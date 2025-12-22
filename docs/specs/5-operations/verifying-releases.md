# Verifying Releases

We sign every official Lychee release with Sigstore cosign to protect you against tampering and supply‑chain attacks. This page shows how to verify the authenticity and integrity of the `Lychee.zip` you download.

## Why code signing matters

- Integrity: Detects any change to the release archive (malicious or accidental).
- Authenticity: Proves the archive was produced by the Lychee maintainers, not a third party.
- Supply‑chain safety: Mitigates risks from compromised mirrors/CDNs or man‑in‑the‑middle attacks.
- Update trust: Lets automation (scripts, package managers, containers) gate installs on verified artifacts.
- Auditability: Cosign can record signatures in a public transparency log for independent verification.

## Files you need from a release

From the GitHub Releases page:
- `Lychee.zip` — the application archive
- `Lychee.zip.sigstore.json` — the cosign signature bundle for that exact archive

Our public verification key is published at:
- GitHub: https://github.com/LycheeOrg/LycheeOrg.github.io/blob/master/public/lychee-cosign.pub
- Website: https://lycheeorg.dev/lychee-cosign.pub

Download the public key once and keep it in a safe location (e.g., `lychee-cosign.pub`).

## Install cosign

Choose one of the options below:

- Homebrew (macOS/Linux):
```bash
brew install sigstore/tap/cosign
```

- Go (any platform):
```bash
go install github.com/sigstore/cosign/v3/cmd/cosign@latest
```

See: https://docs.sigstore.dev/cosign/installation/

## Verify the release

Run the verification from the directory containing the three files:

```bash
cosign verify-blob \
	--key lychee-cosign.pub \
	--bundle Lychee.zip.sigstore.json \
	Lychee.zip
```

Expected output includes a "Verified OK" line. If verification fails, do not install the archive; re‑download both `Lychee.zip` and its matching `Lychee.zip.sigstore.json` and try again.

Tips:
- Ensure the `.sigstore.json` file matches the exact `Lychee.zip` version you are verifying.
- Always download over HTTPS and from our official release page.

> Note: The signature bundle contains all the data needed to verify the artifact's integrity with your local public key. Depending on your environment, cosign may also attempt online transparency log checks. Consult `cosign verify-blob --help` if your environment blocks egress.

## Why we use cosign instead of only PGP

PGP signatures can provide integrity and authenticity, but cosign adds modern supply‑chain protections and better UX:

- Transparency log (Rekor): Cosign can record signatures in a public, append‑only log, making hidden key misuse or signature replacement much harder. PGP has no equivalent, and users often cannot audit history easily.
- CI integration and policy: Cosign fits naturally into CI/CD (attestations, provenance, policies like "only accept signatures from this key/identity").
- Simpler verification: A single binary and a readable bundle (`.sigstore.json`) make verification approachable without managing complex keyrings or web‑of‑trust chains.
- Rich metadata: Cosign signatures can carry metadata/attestations (e.g., build info), enabling future automated checks beyond raw signature validity.
- Flexible key models: Cosign supports traditional keys, hardware keys, and keyless signing (OIDC certificates). This repo currently uses a key‑based flow, but we can migrate without changing the user verification workflow.

In short, PGP ensures "this file hasn't changed since signing," but cosign layers in transparency, policy, and modern workflows that are better aligned with today's software‑supply‑chain practices.

## How we sign releases (for transparency)

We sign the release archive in CI and publish the signature bundle alongside the artifact. In simplified form:

```yaml
steps:
	- name: Install Cosign
		uses: sigstore/cosign-installer@faadad0cce49287aee09b3a48701e75088a2c6ad # v4.0.0

	- name: Download generated artifact
		uses: actions/download-artifact@634f93cb2916e3fdff6788551b99b062d0335ce0 # v5.0.0
		with:
			name: Lychee.zip

	# https://docs.github.com/en/actions/security-guides/security-hardening-for-github-actions#using-an-intermediate-environment-variable
	- name: Sign release with a key
		run: |
			cosign sign-blob --yes --key env://COSIGN_PRIVATE_KEY --bundle Lychee.zip.sigstore.json Lychee.zip
		env:
			COSIGN_PRIVATE_KEY: ${{ secrets.COSIGN_PRIVATE_KEY }}
			COSIGN_PASSWORD: ${{ secrets.COSIGN_PASSWORD }}

	- name: Create release
		uses: softprops/action-gh-release@6da8fa9354ddfdc4aeace5fc48d7f679b5214090 # v2.4.1
		with:
			files: |
				Lychee.zip.sigstore.json
				Lychee.zip
			token: ${{ secrets.GITHUB_TOKEN }}
			generate_release_notes: true
			make_latest: true
```

## Troubleshooting

- "no matching entries were found" or verification fails:
	- Ensure the `.sigstore.json` file corresponds to the same `Lychee.zip` you're verifying.
	- Re‑download from the official release page; avoid third‑party mirrors.
	- Confirm you're using our official public key.
- Network‑restricted environments: Review `cosign verify-blob` flags in the help output to adjust transparency‑log checks when offline.

---

*Last updated: December 22, 2025*
