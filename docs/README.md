# Lychee Documentation

Welcome to the Lychee documentation! This folder contains comprehensive documentation about the Lychee photo management system andis primarily focused on the internal workings of Lychee.
This includes its architecture, request lifecycles, data structures, and validation systems. It is intended for developers and contributors who want to understand how Lychee operates under the hood.

Before getting started, we would like to highlight the folders of interested at the root level of the repository:

```
./
├── app/        # Contains the core of Lychee: models, controllers, actions, and business logic.
├── database/   # Database migrations for setting up the database schema
├── docs/       # Developer documentation (this folder) explaining Lychee's internal workings
├── lang/       # Translation files for internationalization
├── resources/  # Frontend assets including Vue.js components
├── routes/     # API and web route definitions
└── tests/      # Automated test suites for ensuring code quality and functionality
```

The missing folders (`bootstrap`, `composer-cache`, `config`, `docker`, `phpstan`, `public`, `scripts` and `storage`) are still essential for the overall functionality of Lychee but not of matter.

## Getting Started

- If you're looking to understand how Lychee works internally, start with the [Architecture documentation](backend/README.md)
- If you want to contribute to the project, check out our [Contribution guide](Contribute.md)
- For frontend development and Vue.js architecture, see the [Frontend Documentation](frontend/README.md) and our [Vue3 guide](frontend/Vue3.md).

### Albums and data structures

- [Album Tree Structure](backend/Album-tree-structure.md) - How Lychee implements hierarchical album organization
- [Smart Albums Documentation](../app/SmartAlbums/README.md) - Virtual albums that dynamically contain photos based on criteria like starred, recent, or unsorted

### Authorization and Validation
- [Policies Documentation](../app/Policies/README.md) - Authorization and access control system with regular and query policies
- [Rules Documentation](../app/Rules/README.md) - Custom validation rules with patterns, security considerations, and implementation examples

### Localization and Internationalization

- [Localization Documentation](Localization.md) - Translation management, Weblate integration, and maintaining multilingual support

## Additional Resources

For more information about Lychee:
- [Main Repository](https://github.com/LycheeOrg/Lychee)
- [Official Website](https://lycheeorg.dev/)
- [Admin Documentation](https://lycheeorg.dev/docs/)