# Lychee Overview

## What is Lychee?

Lychee is a self-hosted photo management system designed to be elegant, simple, and user-friendly. It transforms your web server into a professional photo gallery and portfolio platform, giving you complete control over your images while providing a polished interface for sharing them with the world.

Unlike cloud-hosted services, Lychee runs on your infrastructure—whether that's a personal server, web hosting, or enterprise environment—ensuring your photos remain under your control.

## Who is Lychee For?

### Primary Audiences

**Professional Photographers**
- Portfolio showcase and client galleries
- Watermarking and copyright protection
- E-commerce integration (webshop in development)
- Support for large collections (100,000+ photos)
- Granular access control for client sharing

**Privacy-Conscious Users**
- Self-hosted alternative to Google Photos, Flickr, or iCloud
- Complete data ownership and control
- No tracking, no data mining, no terms of service changes
- EXIF metadata control (show what you want)

**Teams and Organizations**
- Multi-user photo management with group-based access
- OAuth2 integration for enterprise single sign-on
- Nested permission structures for complex sharing scenarios
- Scalable for organizational photo libraries

**Families and Personal Use**
- Elegant interface for organizing and sharing memories
- Album-based organization with flexible tagging
- Secure sharing with friends and family
- Self-hosted privacy without subscription fees

## Key Capabilities

- **Flexible Organization**: Albums with many-to-many photo relationships, tagging, and nested access structures
- **Sharing & Permissions**: Album-level access control with granular sharing options
- **EXIF Management**: Extract and control photo metadata visibility
- **Professional Features**: Watermarking, portfolio mode, upcoming e-commerce integration
- **Multi-User Support**: Team collaboration with OAuth2 authentication
- **Self-Hosted**: Deploy on your own infrastructure for complete control
- **Mobile-Friendly**: Responsive interface for access from any device

## How Lychee Compares

| Aspect | Lychee | Google Photos | Nextcloud Photos | PhotoPrism/Immich |
|--------|--------|---------------|------------------|-------------------|
| **Hosting** | Self-hosted | Cloud only | Self-hosted | Self-hosted |
| **Focus** | Portfolio/showcase | Personal backup | File management add-on | Personal library |
| **Multi-user** | ✅ Full support | ❌ Family only | ⚠️ Family only | ⚠️ Family only |
| **Professional** | ✅ Portfolio-first | ❌ Consumer | ❌ Not focused | ❌ Not focused |
| **AI Features** | ⚠️ Limited | ✅ Extensive | ⚠️ Basic | ✅ Advanced |
| **Access Control** | ✅ Granular nested | ⚠️ Basic | ⚠️ Basic | ⚠️ Basic |

**Lychee's niche**: Professional portfolio and showcase platform with enterprise-grade permissions, bridging the gap between personal photo management and professional photography needs.

## Project Status

**Maturity**: Production-ready and actively maintained since 2014  
**Governance**: Community-driven with 2 core maintainers and a small reviewer team  
**Development**: Active, with pace limited by volunteer maintainer availability  
**Editions**: Three tiers available
- **Free**: Core open-source features for personal and professional use
- **Supporter Edition (SE)**: Enhanced features supporting development
- **Pro** (coming soon): Advanced enterprise capabilities

Learn more about editions at [lycheeorg.dev/get-supporter-edition](https://lycheeorg.dev/get-supporter-edition/)

## Technical Context

**Architecture**:
- **Backend**: PHP 8.4+ with Laravel framework
- **Frontend**: Vue3 Composition API with TypeScript and PrimeVue components
- **Build**: Vite for modern frontend bundling
- **Database**: MySQL, PostgreSQL, or SQLite

**Deployment Options**:
1. **Docker** (recommended): Pre-configured with all dependencies via [LycheeOrg/Lychee-Docker](https://github.com/LycheeOrg/Lychee-Docker)
2. **Manual installation**: For users comfortable managing web servers and databases

**Requirements**:
- PHP 8.4+ / Laravel framework
- Node.js 20+ (for frontend build)
- Web server (Apache/Nginx)
- Database (MySQL, PostgreSQL, SQLite)
- Basic server administration knowledge (for manual installation)

**Scale**: Tested with professional collections of 100,000+ photos in multi-user setups

## Where to Go Next

**New to Lychee?**
- [Installation Guide](../2-how-to/) - Get Lychee running (Docker recommended)
- [Core Concepts](../1-concepts/) - Understand albums, permissions, and organization

**Developers & Contributors**
- [Coding Conventions](../3-reference/coding-conventions.md) - PHP, Vue3, and testing standards
- [Contributing Guide](../../docs/Contribute.md) - Development setup and workflow
- [Architecture](../4-architecture/) - System design and feature planning

**Professional Users**
- [Website](https://lycheeorg.dev) - Official documentation and resources
- [Supporter Edition](https://lycheeorg.dev/get-supporter-edition/) - Enhanced features

**Support & Community**
- [Discord](https://discord.gg/X4hKPKU) - Real-time community support
- [GitHub Issues](https://github.com/LycheeOrg/Lychee/issues) - Bug reports and feature requests

## Mission Statement

Lychee exists to provide photographers and privacy-conscious users with a professional-grade, self-hosted alternative to cloud photo services. We believe you should own your photos, control how they're shared, and have the tools to showcase them beautifully—all without sacrificing privacy or depending on corporate platforms.

---

*Last updated: December 21, 2025*
