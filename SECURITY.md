# Security Policy

## Supported Versions

Lychee uses a rolling release system, **we do not backport fixes to previously released versions**.
Those are the versions where we accept vulnerability reports.

| Version          | Supported          |
| ---------------- | ------------------ |
| master           | :heavy_check_mark: |
| latest release   | :white_check_mark: |
| < latest release | :x:                |
| < 7.0            | :x:                |

## Reporting a Vulnerability

As described in our [contribution guide][1], if you discover a security vulnerability within Lychee,
please contact us directly on [discord][3]. All security vulnerabilities will be promptly addressed.

[1]: https://lycheeorg.dev/docs/contributions.html#security-vulnerabilities
[3]: https://discord.gg/JMPvuRQcTf

## About the api/v2/Diagnostics endpoint

If you are thinking about reporting an issue regarding the `api/v2/Diagnostics` endpoint,
please note that **it is intentionally public and does not require authentication**.
The responses from this endpoint do not contain any sensitive information or secrets and have been anonymized.

Its main goal is to allow users to easily diagnose issues with their Lychee installation even if they can't log in.

## About `fopen` and DNS Rebinding

We do **not** accept vulnerability reports related to DNS rebinding attacks on `fopen` when `USE_FOPEN_FOR_URL_IMPORTS` is enabled.

We know that this setting switches URL-based imports from `curl` to PHP's native `fopen`. Unlike `curl`, `fopen` performs its own DNS resolution with no built-in mechanism to detect or prevent DNS rebinding — there is no supported way to mitigate this at the application level while still using `fopen`.

Deliberately enabling `USE_FOPEN_FOR_URL_IMPORTS` and then expecting the system to be immune to DNS rebinding is not a valid threat model. **Putting the system into a known-vulnerable configuration and then reporting the resulting exposure is not considered a security vulnerability in Lychee.**