<div align="center">
<img sizes="50%" src='./dragon-lychee-50.png'><br>
<i>image generated via Gemini.</i>
</div>

# Lychee Documentation

Welcome to the Lychee documentation! This folder contains comprehensive documentation about the Lychee photo management system and is primarily focused on the internal workings of Lychee.
This includes its architecture, request lifecycles, data structures, and validation systems. It is intended for developers and contributors who want to understand how Lychee operates under the hood.

Before getting started, we would like to highlight the folders of interest at the root level of the repository:

```
./
├── app/        # Contains the core of Lychee: models, controllers, actions, and business logic
├── database/   # Database migrations for setting up the database schema
├── docs/       # Developer documentation (this folder) explaining Lychee's internal workings
│   └── specs/  # Structured documentation following Diátaxis framework
├── lang/       # Translation files for internationalization
├── resources/  # Frontend assets including Vue.js components
├── routes/     # API and web route definitions
└── tests/      # Automated test suites for ensuring code quality and functionality
```

The missing folders (`bootstrap`, `composer-cache`, `config`, `docker`, `phpstan`, `public`, `scripts` and `storage`) are still essential for the overall functionality of Lychee but not of primary concern for understanding the system.

## Documentation Structure

Documentation is organized following the [Diátaxis framework](https://diataxis.fr/) in the `specs/` directory:

- **[0-overview](specs/0-overview/)** - High-level project documentation
- **[1-concepts](specs/1-concepts/)** - Conceptual explanations (domain model, photos, albums, permissions)
- **[2-how-to](specs/2-how-to/)** - Practical how-to guides
- **[3-reference](specs/3-reference/)** - Technical reference documentation
- **[4-architecture](specs/4-architecture/)** - Architecture decisions and designs
- **[5-operations](specs/5-operations/)** - Operational runbooks
- **[6-decisions](specs/6-decisions/)** - Architectural Decision Records (ADRs)

## Getting Started

### Backend Architecture
- [Backend Architecture](specs/4-architecture/backend-architecture.md) - Laravel structure, design patterns, and key components
- [API Design](specs/3-reference/api-design.md) - RESTful API patterns, authentication, and response structure
- [Database Schema](specs/3-reference/database-schema.md) - Models, relationships, smart albums vs regular albums
- [Request Lifecycle: Album Creation](specs/4-architecture/request-lifecycle-album-creation.md) - Complete album creation flow
- [Request Lifecycle: Photo Upload](specs/4-architecture/request-lifecycle-photo-upload.md) - Photo upload and processing flow

### Frontend Architecture
- [Frontend Architecture](specs/3-reference/frontend-architecture.md) - Vue3, TypeScript, Pinia, composables, and Lychee conventions
- [Frontend Gallery Views](specs/3-reference/frontend-gallery.md) - Gallery viewing modes and component architecture
- [Frontend Layout System](specs/3-reference/frontend-layouts.md) - Photo layout algorithms

### Data Structures
- [Album Tree Structure](specs/4-architecture/album-tree-structure.md) - Nested set model for hierarchical album organization
- [Tag System](specs/4-architecture/tag-system.md) - Tag architecture and operations
- [Smart Albums Documentation](../app/SmartAlbums/README.md) - Virtual albums that dynamically contain photos based on criteria

### Authorization and Validation
- [Policies Documentation](../app/Policies/README.md) - Authorization and access control system with regular and query policies
- [Rules Documentation](../app/Rules/README.md) - Custom validation rules with patterns, security considerations, and implementation examples

### Localization and Internationalization
- [Localization Reference](specs/3-reference/localization.md) - Technical reference for the translation system
- [Translating Lychee](specs/2-how-to/translating-lychee.md) - How-to guide for adding translations

### Contributing
- [Contribution Guide](Contribute.md) - How to contribute to Lychee
- [Coding Conventions](specs/3-reference/coding-conventions.md) - PHP and Vue3 coding standards

## Additional Resources

For more information about Lychee:
- [Main Repository](https://github.com/LycheeOrg/Lychee)
- [Official Website](https://lycheeorg.dev/)
- [Admin Documentation](https://lycheeorg.dev/docs/)
- [Knowledge Map](specs/4-architecture/knowledge-map.md) - Module and dependency relationships

---

*Last updated: December 22, 2025*