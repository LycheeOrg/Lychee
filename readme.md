<p align="center"><img src="https://raw.githubusercontent.com/LycheeOrg/Lychee-Laravel/master/Banner.png" width="400px" alt="@LycheeOrg"></p>

<p align="center">
<a href="https://travis-ci.com/LycheeOrg/Lychee-Laravel"><img src="https://travis-ci.com/LycheeOrg/Lychee-Laravel.svg?branch=master" alt="Build Status"></a>
<a href="https://codecov.io/gh/LycheeOrg/Lychee-Laravel"><img src="https://codecov.io/gh/LycheeOrg/Lychee-Laravel/branch/master/graph/badge.svg" alt="Code Coverage"></a>
<a href="https://github.com/LycheeOrg/Lychee-Laravel/releases"><img alt="GitHub release" src="https://img.shields.io/github/release-pre/LycheeOrg/Lychee-Laravel.svg"></a>
<a href="https://github.com/LycheeOrg/Lychee-Laravel/blob/master/LICENSE"><img alt="GitHub" src="https://img.shields.io/github/license/LycheeOrg/Lychee-Laravel.svg"></a>
<a href="https://gitter.im/LycheeOrg/Lobby"><img src="https://img.shields.io/gitter/room/LycheeOrg/Lobby.svg?logo=gitter" alt="Gitter"></a>
<a href="https://bestpractices.coreinfrastructure.org/projects/2855"><img alt="CII Best Practices Summary" src="https://img.shields.io/cii/summary/2855.svg"></a>
</p>


#### A great looking and easy-to-use photo-management-system.

*Since the 1st of April 2018 this project has moved to it's own Organisation (https://github.com/LycheeOrg) where people are able to submit their fixes to it. We, the Organisation owners, want to thank electerious (Tobias Reich) for the opportunity to make this project live on.*

![Lychee](https://camo.githubusercontent.com/b9010f02c634219795950e034f511f4cf4af5c60/68747470733a2f2f732e656c6563746572696f75732e636f6d2f696d616765732f6c79636865652f312e6a706567)
![Lychee](https://camo.githubusercontent.com/5484591f0b15b6ba27d4845b292cc5d3a988b3b9/68747470733a2f2f732e656c6563746572696f75732e636f6d2f696d616765732f6c79636865652f322e6a706567)

Lychee is a free photo-management tool, which runs on your server or web-space. Installing is a matter of seconds. Upload, manage and share photos like from a native application. Lychee comes with everything you need and all your photos are stored securely. Read more on our [website](https://LycheeOrg.github.io).

## Installation

To run Lychee, everything you need is a web-server with PHP 7.3 or later and a MySQL-Database. Follow the instructions to install Lychee on your server. This version of Lychee is built on the Laravel framework. To install:

1. Clone this repo to your server and set the web root to `lychee-laravel/public`
2. Run `composer install --no-dev` to install dependencies
3. Copy `.env.example` as `.env` and edit it to match your parameters
4. Generate your secret key with `php artisan key:generate`
5. Migrate your database with `php artisan migrate` to create a new database or migrate an existing Lychee installation to the new framework.

See detailed instructions on the [Installation](https://github.com/LycheeOrg/Lychee-Laravel/wiki/Install) wiki page.

### Docker

Alternatively, an official Docker image can be found at [LycheeOrg/Lychee-Laravel-Docker](https://github.com/LycheeOrg/Lychee-Laravel-Docker) or on Docker Hub as [bigrob8181/lychee-laravel](https://hub.docker.com/r/bigrob8181/lychee-laravel).

### Update

Updating is as easy as it should be.  [Update &#187;](https://github.com/LycheeOrg/Lychee-Laravel/wiki/Update)

### Settings

Sign in and click the gear in the top left corner to change your settings. [Settings &#187;](https://github.com/LycheeOrg/Lychee-Laravel/wiki/Settings)

### Build

Lychee is ready to use, right out of the box. If you want to contribute and edit CSS or JS files, you need to rebuild [Lychee-front](https://github.com/LycheeOrg/Lychee-front). [Build &#187;](https://github.com/LycheeOrg/Lychee-Laravel/wiki/Build)

## Advanced Features

Lychee is ready to use straight after installation, but some features require a little more configuration.

### Keyboard Shortcuts

These shortcuts will help you to use Lychee even faster. [Keyboard Shortcuts &#187;](https://github.com/LycheeOrg/Lychee-Laravel/wiki/Keyboard-Shortcuts)

### Dropbox import

In order to use the Dropbox import from your server, you need a valid drop-ins app key from [their website](https://www.dropbox.com/developers/apps/create). Lychee will ask you for this key, the first time you try to use the import. Want to change your code? Take a look at [the settings](https://github.com/LycheeOrg/Lychee-Laravel/wiki/Settings) of Lychee.

### Twitter Cards

Lychee supports [Twitter Cards](https://dev.twitter.com/docs/cards) and [Open Graph](http://opengraphprotocol.org) for shared images (not albums). In order to use Twitter Cards you need to request an approval for your domain. Simply share an image with Lychee, copy its link and paste it in [Twitters Card Validator](https://dev.twitter.com/docs/cards/validation/validator).

### Imagick

Lychee uses [Imagick](https://www.imagemagick.org) when installed on your server. In this case you will benefit from a faster processing of your uploads, better looking thumbnails and intermediate sized images for small screen devices. You can disable the usage of [Imagick](https://www.imagemagick.org) in the [settings](https://github.com/LycheeOrg/Lychee-Laravel/wiki/Settings).

## Troubleshooting

Take a look at the [Wiki](https://github.com/LycheeOrg/Lychee-Laravel/wiki/), particularly the [FAQ](https://github.com/LycheeOrg/Lychee-Laravel/wiki/FAQ) if you have problems. Discovered a bug? Please create an issue [here](https://github.com/LycheeOrg/Lychee-Laravel/issues) on GitHub!
