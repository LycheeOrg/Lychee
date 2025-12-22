# Core Concepts

This section explains the fundamental concepts and domain model of Lychee. Understanding these concepts is essential for using Lychee effectively or contributing to its development.

## Concept Pages

### Content Management

**[Photos](photos.md)**
- Photo attributes and metadata
- Photo-album relationships
- Photo types (images, videos, raw files)
- Size variants and optimization
- EXIF metadata extraction
- Color palette analysis

**[Albums](albums.md)**
- Album architecture (BaseAlbum hierarchy)
- Regular Albums (hierarchical nested trees)
- Tag Albums (dynamic tag-based collections)
- Smart Albums (system-generated virtual albums)
- Album inheritance and composition patterns

**[Access Permissions](permissions.md)**
- Permission model and grants
- User, group, and public access
- Permission hierarchy and inheritance
- Nested tree permission flow
- Password protection

### User Management

**[Users and Authentication](users.md)**
- User accounts and attributes
- User capabilities and quotas
- User groups and membership
- Authentication methods (password, OAuth, WebAuthn)
- Permission resolution

### Features

**[E-commerce and Webshop](e-commerce.md)**
- Purchasable photos and albums
- Pricing models and size variants
- Order lifecycle and payment processing
- Guest purchases and fulfillment
- Omnipay integration

**[System Features](system.md)**
- Statistics and analytics
- Job history and background tasks
- OAuth credentials and SSO
- System configuration and settings

---

## Core Relationships

```
User
  ├─ owns → Photos (one-to-many)
  ├─ owns → Albums (one-to-many)
  ├─ belongs to → UserGroups (many-to-many with pivot: role, created_at)
  ├─ has → AccessPermissions (one-to-many)
  ├─ has → OauthCredentials (one-to-many)
  ├─ has → JobHistory (one-to-many)
  └─ has → Orders (one-to-many, nullable for guest purchases)

Album
  ├─ contains → Photos (many-to-many via photo_album pivot)
  ├─ has parent → Album (self-referencing)
  ├─ has children → Albums (self-referencing, nested tree)
  ├─ owned by → User (many-to-one)
  ├─ has → AccessPermissions (one-to-many)
  ├─ has → Statistics (one-to-one)
  └─ can be → Purchasable (one-to-one)

Photo
  ├─ belongs to → Albums (many-to-many via photo_album pivot)
  ├─ owned by → User (many-to-one)
  ├─ has → SizeVariants (one-to-many custom relationship)
  ├─ has → Statistics (one-to-one)
  ├─ has → Palette (one-to-one) - color information
  ├─ has → Purchasable (one-to-one) - webshop integration
  └─ tagged with → Tags (many-to-many via photos_tags pivot)

Tag
  └─ applied to → Photos (many-to-many via photos_tags pivot)

AccessPermission
  ├─ targets → Album (many-to-one)
  ├─ grants to → User (optional, many-to-one)
  └─ grants to → UserGroup (optional, many-to-one)

Purchasable
  ├─ for → Photo (optional, many-to-one)
  ├─ for → Album (optional, many-to-one)
  └─ has → PurchasablePrices (one-to-many)

Order
  ├─ belongs to → User (optional, nullable for guests)
  └─ contains → OrderItems (one-to-many)

OrderItem
  ├─ belongs to → Order (many-to-one)
  ├─ for → Photo (many-to-one)
  └─ references → Purchasable (many-to-one)

Statistics
  ├─ tracks → Photo (optional, many-to-one)
  └─ tracks → Album (optional, many-to-one)

Palette
  └─ belongs to → Photo (many-to-one)

OauthCredential
  └─ belongs to → User (many-to-one)

JobHistory
  └─ owned by → User (many-to-one)
```

---

## Next Steps

Now that you understand Lychee's core concepts:

- **New Users**: See [How-To Guides](../2-how-to/) for practical usage instructions
- **Developers**: Review [Coding Conventions](../3-reference/coding-conventions.md) and [Architecture](../4-architecture/)
- **Administrators**: Check [Operations](../5-operations/) for deployment and maintenance

---

*Last updated: December 22, 2025*
