<p align="center"><a href="https://lycheeorg.dev"><img src="https://raw.githubusercontent.com/LycheeOrg/Lychee/master/Banner.png" width="400px" alt="@LycheeOrg"></a></p>

[![GitHub Release][release-shield]](https://github.com/LycheeOrg/Lychee/releases)
[![PHP 8.3 & 8.4][php-shield]](https://lycheeorg.dev/docs/#server-requirements)
[![MIT License][license-shield]](https://github.com/LycheeOrg/Lychee/blob/master/LICENSE)
[![Downloads][download-shield]](https://github.com/LycheeOrg/Lychee/releases)
<br>
[![Build Status][build-status-shield]](https://github.com/LycheeOrg/Lychee/actions)
[![Code Coverage][codecov-shield]](https://codecov.io/gh/LycheeOrg/Lychee)
[![CII Best Practices Summary][cii-shield]](https://bestpractices.coreinfrastructure.org/projects/2855)
[![OpenSSF Scorecard][ossf-shield]](https://securityscorecards.dev/viewer/?uri=github.com/LycheeOrg/Lychee)
[![Quality Gate Status][sonar-shield]](https://sonarcloud.io/dashboard?id=LycheeOrg_Lychee-Laravel)
<br>
[![Website][website-shield]](https://lycheeorg.dev)
[![Documentation][docs-shield]](https://lycheeorg./docs/)
[![Changelog][changelog-shield]](https://lycheeorg.dev/docs/releases.html)
[![Docker repository][docker-shield]](https://github.com/LycheeOrg/Lychee-Docker)
[![Gitter][gitter-shield]](https://gitter.im/LycheeOrg/Lobby)
[![Discord][discord-shield]][discord]

# Lychee: A Stunning and User-Friendly Photo Management System

Lychee is a free, open-source photo-management tool that runs on your server or web space. Installation is quick and easy, taking just seconds. With Lychee, you can upload, manage, and share your photos seamlessly, just like using a native application. It comes with all the essential features you need, ensuring your photos are stored securely. Read more on our [website](https://LycheeOrg.dev).

For even more advanced features, consider the Supporter Edition (SE). The SE version offers additional functionality to enhance your experience. Learn more about the Supporter Edition and its benefits [here](https://lycheeorg.dev/get-supporter-edition).

## Support the Team

We aim to maintain a free open-source photography library with high quality of code.<br>
Being in control of our own data, our own pictures is something that we value above all.

Through [contributions, donations, and sponsorship](https://github.com/sponsors/LycheeOrg), you allow Lychee to thrive. Your donations directly support demo server costs, continuous enhancements, and most importantly bug fixes!

## Installation

There are three deployment options available. The simplest is **Docker deployment**, as all dependencies are already predefined and configured.

### Docker deployment

An official Docker image can be found at [LycheeOrg/Lychee-Docker](https://github.com/LycheeOrg/Lychee-Docker) or on Docker Hub as [lycheeorg/lychee](https://hub.docker.com/r/lycheeorg/lychee).

### File-based deployment

Copy the extracted Zip file from https://github.com/LycheeOrg/Lychee/releases to your webserver

### Build from Source deployment

To run Lychee, everything you need is a web-server with PHP 8.3 or later and a database (MySQL/MariaDB, PostgreSQL or SQLite). Follow the instructions to install Lychee on your server. This version of Lychee is built on the Laravel framework. To install:

1. Clone this repo to your server and set the web root to `lychee/public`
2. Run `composer install --no-dev` to install dependencies
3. Run `npm install` to install node dependencies
4. Run `npm run build` to build the front-end
5. Copy `.env.example` as `.env` and edit it to match your parameters
6. Generate your secret key with `php artisan key:generate`
7. Migrate your database with `php artisan migrate` to create a new database or migrate an existing Lychee installation to the latest framework.

See detailed instructions on the [Installation](https://lycheeorg.dev/docs/installation.html) page of our documentation.

### Update

Updating is as easy as it should be. [Update &#187;](https://lycheeorg.dev/docs/update.html)

## Configuration

### Settings

Sign in and click the gear in the top left corner to change your settings. [Settings &#187;][1]

### Advanced Features

Lychee is ready to use straight after installation, but some features require a little more configuration.

## Documentation

### Keyboard Shortcuts

These shortcuts will help you to use Lychee even faster. [Keyboard Shortcuts &#187;](https://lycheeorg.dev/docs/keyboard.html)

### Dropbox import

In order to use the Dropbox import from your server, you need a valid drop-ins app key from [their website](https://www.dropbox.com/developers/saver). Lychee will ask you for this key, the first time you try to use the import. Want to change your code? Take a look at [the settings][1] of Lychee.

### Twitter Cards

Lychee supports [Twitter Cards](https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/abouts-cards) and [Open Graph](http://opengraphprotocol.org) for shared images (not albums). In order to use Twitter Cards you need to request an approval for your domain. Simply share an image with Lychee, copy its link and paste it in [Twitter's Card Validator](https://cards-dev.twitter.com/validator).

### ImageMagick

Lychee uses [ImageMagick](https://www.imagemagick.org) when installed on your server. In this case you will benefit from a faster processing of your uploads, better looking thumbnails and intermediate sized images for small screen devices. You can disable the usage of [ImageMagick](https://www.imagemagick.org) in the [settings][1].

### New Photos Email Notification

In order to use the new photos email notification you will need to have configured the **MAIL_** variables in your .env to your mail provider & [setup cron](https://laravel.com/docs/scheduling#running-the-scheduler). Once that is complete you then toggle **Send new photos notification emails** in the [settings][1]. Your users will be able to opt-in to the email notifications by entering their email address in the **Notifications** setting in the sidebar. Photo notifications will be grouped and sent out once a week to the site admin, album owner & anyone who the album is shared with, if their email has been added. The admin or user who added the photo to an album, will not receive a email notification for the photos they added.

## Troubleshooting

Take a look at the [Documentation](https://lycheeorg.dev/docs/), particularly the [FAQ](https://lycheeorg.dev/docs/faq_troubleshooting.html) if you have problems. Discovered a bug? Please create an issue [here](https://github.com/LycheeOrg/Lychee/issues) on GitHub! You can also contact us directly on [gitter (login with your github account)](https://gitter.im/LycheeOrg/Lobby) or on [discord &#187;][discord].

## Notice: `master` & `alpha` branches

As LycheeOrg is a very small team, we do not have many maintainers. Most of us have an active work/family life, and as a result, it is no longer possible for us to apply proper 4-eyes principle in the coding reviews.

In order to keep our high code quality, the following changes have been applied.

- `master` stays as a stable branch and contains 4-eyes peer-reviewed pull-requests.
- `alpha` contains the latest changes (i.e. the above mentionned PR) merged with minimal review.

With this change, we hope to strike a balance between decently paced development (on `alpha`) and maintaining a robust core (on `master`).

On Docker, `nightly`/`dev` continues to refer to the latest `master` commit.
The `alpha` tag is updated daily with the content of the associated branch.

That being said, if you like the gallery and would like to contribute, do not hesitate to open pull request. If you would like to see more functionalities added and help us push Lychee, [Join the team!](https://lycheeorg.dev/docs/contributions.html#joining-the-team)

## Open Source Community Support

<img src="https://resources.jetbrains.com/storage/products/company/brand/logos/PhpStorm_icon.png" alt="PhpStorm" width="50"/>

We would like to thank Jetbrains for supporting us with their [Open Source Development - Community Support][jetbrains-opensource] program.

[1]: https://lycheeorg.dev/docs/settings.html
[build-status-shield]: https://img.shields.io/github/actions/workflow/status/LycheeOrg/Lychee/CICD.yml?branch=master
[codecov-shield]: https://codecov.io/gh/LycheeOrg/Lychee/branch/master/graph/badge.svg
[release-shield]: https://img.shields.io/github/release/LycheeOrg/Lychee.svg
[php-shield]: https://img.shields.io/badge/PHP-8.e%20|%208.4-blue
[license-shield]: https://img.shields.io/github/license/LycheeOrg/Lychee.svg
[cii-shield]: https://img.shields.io/cii/summary/2855.svg
[ossf-shield]: https://api.securityscorecards.dev/projects/github.com/LycheeOrg/Lychee/badge
[sonar-shield]: https://sonarcloud.io/api/project_badges/measure?project=LycheeOrg_Lychee-Laravel&metric=alert_status
[website-shield]: https://img.shields.io/badge/-Website-informational.svg?logo=data:image/png;base64,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
[docs-shield]: https://img.shields.io/badge/-Documentation-informational.svg?logo=data:image/png;base64,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
[changelog-shield]: https://img.shields.io/badge/-Changelog-informational.svg?logo=data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABkAAAASCAYAAACuLnWgAAABfWlDQ1BpY2MAACiRfZE9SMNAHMVfU6VaKg52EHEIWJ0siIqIk1ahCBVCrdCqg8mlX9CkIWlxcRRcCw5+LFYdXJx1dXAVBMEPECdHJ0UXKfF/SaFFjAfH/Xh373H3DhDqJaZZHWOAplfMZDwmpjOrYuAVQQTQjSHMyMwy5iQpAc/xdQ8fX++iPMv73J+jR81aDPCJxLPMMCvEG8RTmxWD8z5xmBVklficeNSkCxI/cl1x+Y1z3mGBZ4bNVHKeOEws5ttYaWNWMDXiSeKIqumUL6RdVjlvcdZKVda8J39hKKuvLHOd5iDiWMQSJIhQUEURJVQQpVUnxUKS9mMe/gHHL5FLIVcRjBwLKEOD7PjB/+B3t1ZuYtxNCsWAzhfb/hgGArtAo2bb38e23TgB/M/Ald7yl+vA9CfptZYWOQJ6t4GL65am7AGXO0D/kyGbsiP5aQq5HPB+Rt+UAfpugeCa21tzH6cPQIq6StwAB4fASJ6y1z3e3dXe279nmv39AJMecrRgM3JmAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAC4jAAAuIwF4pT92AAAEPUlEQVQ4y6XU34tUZRgH8O/748yZOWfn7OyuOzuzaquuu/4kUTS1i0S8CFLQCCGEuki680+IroS6CQKjC70JRIXooiDCQEkKRUyxxFCydV1Xx5md2fl1fr/ved+3CyEQLDf83j98eHh4vgRLjDSGy7mZirxxjTNKQCenMza5vm67bvaiWb4UwBhDur/8/LZuLBynnb6joMHo/Uj2uh8ZY74hhJiXRlpXL7+V3rn7hZlrlpkmoAUC6oeg1eBEcPumD+D8SyPNG9d30FqzbPkaJAOsxAJJBYwSY9LO73sRQpeCtFttBO0+0n4A6ccQ3RiyFSJt9SDi6KBfr1WXjEShGAyCZFym5pkNS1u3wI8CxH6EzBeQrQiiFyFthkh7kRZa/+dNCADouqGnb378ZkfeOVYo2NOAfWZicPeZNRfFel7vbFMr83vv/np1D5lPUC4MI6c4mMeReAbOG9uulycrP4gwImxw5Jq3acePzrIR+QxiMlP6+sInxy7fO32sKWbHuK0wlK+qve2DC9O/ea4D4+VKFvomwszMAwQLbZQsF8o26OZSrNo1hfFqGUYIaLu4KFeXvw03Jt9Bl37aPnkkAACaJWagHy68V28/GKPKRp67qPaHWfmKqNJHvmcCAdMRGDQFbN48DWuFh/OP7uCPsIGRMRdOEiJrL0AuLiKceTjSuXn96J/3vjo30zp76uLvn40BALUG6CNOimdLhSq4JVEd8rD2yUrYTQMVC4huAtFLIdspaM9g8+QUXtv5KrZuWItRzwOnFqTS8IMYfitANBNAzKduI7j6bpDc/7zZ6HgcAHauP3wy5bW99ezCHscMgzxmSJUEIRTQgEk0aIHAEhQcNqZWjCMOulAA0iQGEgL/SQqSMWQOkIkMQZwg5Xq5MWmOA0BAZx1SaC1XPY4oU4gtCaEzQFIQRmEBIJYCcQkyxyCJJQQkuM7BhBpZoqA1AWEGwgX8vB2Mu/vvVQa3f1quVFocAMKoua/ndypEOQDyeOx0USmOggcEqVIAKLjRSOwMERN4GC4i60SYcIrghkFJQOUZYgawdVN4fdPhk8WV48c3VHb1gKNPPz4I/HO7Jz9sdKIn28K4X4o2qg/mag8GqorCQw4wEjLUoIKg2ewgLo7BuBR/zc1jwGLQFpBwjfya1ZjeuhvV4aktXOY0IUT/8yfPVMj84/eDXvfUrUuXcvVLN5Cv+3AZAy9Q9EyCeMjGxkOHML5uHbqNBhLfB2EEec+DNzKKvOtqyugtxvj+yqqJ2nOR2uzsAQJywmj9StTzqd9oIu33YbQCt20MVstwly3zmWU1CHBFa1MDQdFo7WutBeV8kXH+fc6254bKo+q5SP3hHDPKrCKEHKGUvQOjR562vQEhhAK4bYAvLdu+VigOtPOOI+Mg5ACUUxzQ/1orz0uv3WFKiHKWprbRT2cJYyCUdkdXLO/if+Rvf2QoDtYrAMIAAAAASUVORK5CYII=
[docker-shield]: https://img.shields.io/badge/-Lychee--Docker-informational.svg?logo=github
[gitter-shield]: https://img.shields.io/gitter/room/LycheeOrg/Lobby.svg?logo=gitter
[jetbrains-opensource]: https://www.jetbrains.com/community/opensource/
[download-shield]: https://img.shields.io/github/downloads/LycheeOrg/Lychee/total
[discord]: https://discord.gg/JMPvuRQcTf
[discord-shield]: https://img.shields.io/discord/1046911561366765598?logo=discord
