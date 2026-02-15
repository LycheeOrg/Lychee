<p align="center"><a href="https://lycheeorg.dev"><img src="https://raw.githubusercontent.com/LycheeOrg/Lychee/master/Banner.png" width="400px" alt="@LycheeOrg"></a></p>

[![GitHub Release][release-shield]](https://github.com/LycheeOrg/Lychee/releases)
[![PHP 8.4 & 8.5][php-shield]](https://lycheeorg.dev/docs/#server-requirements)
[![MIT License][license-shield]](https://github.com/LycheeOrg/Lychee/blob/master/LICENSE)
[![Downloads][download-shield]](https://github.com/LycheeOrg/Lychee/releases)
<br>
[![Build Status][build-status-shield]](https://github.com/LycheeOrg/Lychee/actions)
[![Code Coverage][codecov-shield]](https://codecov.io/gh/LycheeOrg/Lychee)
[![CII Best Practices Summary][cii-shield]](https://bestpractices.coreinfrastructure.org/projects/2855)
[![OpenSSF Scorecard][ossf-shield]](https://securityscorecards.dev/viewer/?uri=github.com/LycheeOrg/Lychee)
<br>
[![Website][website-shield]](https://lycheeorg.dev)
[![Documentation][docs-shield]](https://lycheeorg.dev/docs/)
[![Changelog][changelog-shield]](https://lycheeorg.dev/docs/releases.html)
[![Gitter][gitter-shield]](https://gitter.im/LycheeOrg/Lobby)
[![Discord][discord-shield]][discord]

# Lychee: A Stunning and User-Friendly Photo Management System

Lychee is a free, open-source photo-management tool that runs on your server or web space. Installation is quick and easy, taking just seconds. With Lychee, you can upload, manage, and share your photos seamlessly, just like using a native application. It comes with all the essential features you need, ensuring your photos are stored securely. Read more on our [website](https://LycheeOrg.dev).

For even more advanced features, consider the Supporter Edition (SE). The SE version offers additional functionality to enhance your experience. Learn more about the Supporter Edition and its benefits [here](https://lycheeorg.dev/get-supporter-edition).

## ⚠️ Upgrade Notice to Version 7.0

**Version 7.0 introduces significant changes on the docker image.** Please refer to our [Upgrade Guide](https://lycheeorg.dev/docs/upgrade.html#upgrading-lychee-docker-installations-from-v6-to-v7) for detailed instructions on how to upgrade from previous versions.

## Support the Team

We aim to maintain a free open-source photography library with high quality of code.<br>
Being in control of our own data, our own pictures is something that we value above all.

Through [contributions, donations, and sponsorship](https://github.com/sponsors/LycheeOrg), you allow Lychee to thrive. Your donations directly support demo server costs, continuous enhancements, and most importantly bug fixes!

## Contributing

Contributions welcome! Check out our [Contribution Guide](docs/Contribute.md) and [documentation](https://github.com/LycheeOrg/Lychee/tree/master/docs) for setup, coding standards, and PR guidelines.

**AI-assisted contributions** are permitted — see our [AI/Claude Guidelines](docs/Contribute.md#using-aiclaude-for-contributions) and [AGENTS.md](AGENTS.md) for the Specification-Driven Development workflow.

## Installation

### Quick Try (Docker)

Want to quickly test Lychee? Use the minimal docker-compose template:

```bash
curl -O https://raw.githubusercontent.com/LycheeOrg/Lychee/master/docker-compose.minimal.yaml
docker compose -f docker-compose.minimal.yaml up -d
```

Then open http://localhost:8000 in your browser. This setup includes a separate worker container for background jobs.

### Docker (Recommended)

The easiest way to deploy Lychee with all dependencies configured:

```yaml
services:
  lychee:
    image: ghcr.io/lycheeorg/lychee:latest
    container_name: lychee
    ports:
      - "8000:8000"
    volumes:
      - ./lychee/uploads:/app/public/uploads
      - ./lychee/logs:/app/storage/logs
      - ./lychee/tmp:/app/storage/tmp
    environment:
      APP_URL: http://localhost:8000
      DB_CONNECTION: mysql
      DB_HOST: lychee_db
      DB_PORT: 3306
      DB_DATABASE: lychee
      DB_USERNAME: lychee
      DB_PASSWORD: lychee_password
	  # Generate the APP_KEY with `echo "base64:$(openssl rand -base64 32)"` and set it here (without the < >)
      # APP_KEY: base64:<result of 'openssl rand -base64 32'>
    depends_on:
      lychee_db:
        condition: service_healthy
    restart: unless-stopped

  lychee_db:
    image: mariadb:11
    container_name: lychee_db
    environment:
      MYSQL_DATABASE: lychee
      MYSQL_USER: lychee
      MYSQL_PASSWORD: lychee_password
      MYSQL_ROOT_PASSWORD: root_password
    volumes:
      - lychee_db:/var/lib/mysql
    healthcheck:
      test: ["CMD", "healthcheck.sh", "--connect", "--innodb_initialized"]
      interval: 5s
      timeout: 3s
      retries: 10
    restart: unless-stopped

volumes:
  lychee_db:
```

**Images:** [GitHub Container Registry](https://github.com/LycheeOrg/Lychee/pkgs/container/lychee) | [Docker Hub](https://hub.docker.com/r/lycheeorg/lychee)

**Docker Tags:**
- `latest` - Latest stable release
- `edge` - Latest development build from master

### Other Installation Methods

- **Pre-built releases:** Download from [GitHub Releases](https://github.com/LycheeOrg/Lychee/releases)
- **From source:** Requires PHP 8.4+, Composer, and npm

For detailed installation, configuration, and update instructions, see our **[Documentation](https://lycheeorg.dev/docs/)**.

## Troubleshooting

- **[Documentation](https://lycheeorg.dev/docs/)** - Complete guides and FAQ
- **[GitHub Issues](https://github.com/LycheeOrg/Lychee/issues)** - Report bugs
- **[Discord][discord]** or **[Gitter](https://gitter.im/LycheeOrg/Lobby)** - Community support

## Open Source Community Support

<img src="https://resources.jetbrains.com/storage/products/company/brand/logos/PhpStorm_icon.png" alt="PhpStorm" width="50"/>

We would like to thank Jetbrains for supporting us with their [Open Source Development - Community Support][jetbrains-opensource] program.

[build-status-shield]: https://img.shields.io/github/actions/workflow/status/LycheeOrg/Lychee/CICD.yml?branch=master
[codecov-shield]: https://codecov.io/gh/LycheeOrg/Lychee/branch/master/graph/badge.svg
[release-shield]: https://img.shields.io/github/release/LycheeOrg/Lychee.svg
[php-shield]: https://img.shields.io/badge/PHP-8.4%20|%208.5-blue
[license-shield]: https://img.shields.io/github/license/LycheeOrg/Lychee.svg
[cii-shield]: https://img.shields.io/cii/summary/2855.svg
[ossf-shield]: https://api.securityscorecards.dev/projects/github.com/LycheeOrg/Lychee/badge
[website-shield]: https://img.shields.io/badge/-Website-informational.svg?logo=data:image/png;base64,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
[docs-shield]: https://img.shields.io/badge/-Documentation-informational.svg?logo=data:image/png;base64,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
[changelog-shield]: https://img.shields.io/badge/-Changelog-informational.svg?logo=data:image/png;base64,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
[gitter-shield]: https://img.shields.io/gitter/room/LycheeOrg/Lobby.svg?logo=gitter
[jetbrains-opensource]: https://www.jetbrains.com/community/opensource/
[download-shield]: https://img.shields.io/github/downloads/LycheeOrg/Lychee/total
[discord]: https://discord.gg/JMPvuRQcTf
[discord-shield]: https://img.shields.io/discord/1046911561366765598?logo=discord
