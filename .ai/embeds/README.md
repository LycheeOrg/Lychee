# Embeddable Photo Album Widget - Project Documentation

This directory contains the complete specification and implementation plan for Lychee's embeddable photo album widget feature.

## Document Overview

### [SPECIFICATION.md](./SPECIFICATION.md)
**Purpose**: Defines what the feature does from a user and business perspective

**Contents**:
- Feature description and goals
- User stories
- UI/UX specifications
- API specifications
- Access control and security requirements
- Browser support and performance targets
- Success metrics

**Audience**: Product managers, designers, stakeholders, QA

### [IMPLEMENTATION.md](./IMPLEMENTATION.md)
**Purpose**: Technical blueprint for building the feature

**Contents**:
- Architecture overview
- Detailed backend implementation
- Detailed frontend widget implementation
- Complete layout algorithms
- Build and distribution strategy
- Testing strategy
- Implementation timeline
- File checklist

**Audience**: Developers, technical leads

### [RESEARCH.md](./RESEARCH.md) *(See task research)*
**Purpose**: Research findings about Lychee's existing layout system

**Contents**: The comprehensive research conducted during planning includes:
- Existing layout type implementations
- Responsive column calculation formulas
- Size variant selection logic
- Configuration settings and defaults
- Component hierarchy and integration

**Note**: This research is documented in the initial exploration task and can be extracted into a separate document if needed.

## Quick Reference

### What This Feature Does

Allows Lychee users to embed their public photo albums on external websites using a simple copy/paste JavaScript widget. The widget supports all 5 gallery layout types:

1. **Square** - Perfect square thumbnails in a grid
2. **Justified** - Flickr-style rows with preserved aspect ratios
3. **Masonry** - Pinterest-style columns
4. **Grid** - Regular grid with aspect ratios
5. **Filmstrip** - Large photo with thumbnail navigation strip

### Key Features

- ✅ All 5 Lychee layout types supported
- ✅ Configurable thumbnail sizes with responsive column calculation
- ✅ Full-screen lightbox viewer
- ✅ Album metadata and photo EXIF display
- ✅ Light/dark themes
- ✅ Keyboard and touch navigation
- ✅ WCAG 2.1 Level AA accessible
- ✅ < 100KB bundle size
- ✅ Public albums only (no password support)

### Technology Stack

**Backend**:
- Laravel 12.x (PHP 8.4+)
- New API endpoint: `GET /api/v2/Embed/{albumId}`
- CORS configuration for cross-origin access

**Frontend Widget**:
- TypeScript 5.x
- Vanilla JavaScript (no frameworks)
- justified-layout v4.1.0 library
- Vite 7.x for building
- CSS3 with custom properties

**Frontend UI (Lychee)**:
- Vue 3 component for embed code generator
- Integration in album view

### Implementation Timeline

- **Week 1**: Backend API foundation
- **Week 2**: Widget core and layouts (Square, Masonry, Grid, Justified)
- **Week 3**: Filmstrip layout and lightbox
- **Week 4**: UI integration and embed code generator
- **Week 5**: Polish, accessibility, testing, documentation

**Total**: ~5 weeks for full implementation

### Key Technical Decisions

1. **JavaScript widget** (not iframe) for better integration
2. **Vanilla JS/TypeScript** (no Vue in widget) for smaller bundle
3. **Absolute positioning** for layouts (matching Lychee's approach)
4. **justified-layout library** for justified layout (same as Lychee uses)
5. **CSS variables** for flexible theming
6. **Public albums only** (security and simplicity)

## Architecture at a Glance

```
External Website
    │
    └─> LycheeEmbed Widget (JavaScript)
            │
            ├─> API Call: GET /api/v2/Embed/{albumId}
            │       │
            │       └─> Lychee Server
            │               ├─> Validate public access
            │               ├─> Fetch album + photos
            │               └─> Return JSON with photo URLs
            │
            ├─> Layout Manager
            │       ├─> Square Layout
            │       ├─> Justified Layout
            │       ├─> Masonry Layout
            │       ├─> Grid Layout
            │       └─> Filmstrip Layout
            │
            ├─> Lightbox Viewer
            │
            └─> Album Header
```

## Development Quick Start

### 1. Install Dependencies

```bash
npm install justified-layout @types/justified-layout --save-dev
```

### 2. Backend: Create Embed Controller

```bash
php artisan make:controller Gallery/EmbedController
```

See `IMPLEMENTATION.md` Phase 1.1 for complete code.

### 3. Frontend: Set Up Widget Project

Create directory structure:
```bash
mkdir -p resources/js/embed/{layouts,components,utils,api,styles}
```

Create `vite.embed.config.ts` (see IMPLEMENTATION.md Phase 2.1).

### 4. Build Widget

```bash
npm run build:embed
```

### 5. Test Locally

Create test HTML file:
```html
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="http://localhost:8000/embed/lychee-embed.css">
</head>
<body>
  <div id="lychee-embed"></div>
  <script src="http://localhost:8000/embed/lychee-embed.umd.js"></script>
  <script>
    new LycheeEmbed({
      containerId: 'lychee-embed',
      albumId: 'YOUR_ALBUM_ID',
      baseUrl: 'http://localhost:8000',
      layout: 'justified'
    });
  </script>
</body>
</html>
```

## File Locations

### Backend Files (New)
- `app/Http/Controllers/Gallery/EmbedController.php`
- `app/Http/Resources/Embed/EmbedAlbumResource.php`
- `app/Http/Resources/Embed/EmbedPhotoResource.php`

### Backend Files (Modified)
- `routes/api_v2.php` - Add embed routes
- `config/cors.php` - Configure CORS

### Frontend Widget Files (New)
- `resources/js/embed/` - Complete widget source
- `vite.embed.config.ts` - Build configuration
- `public/embed/` - Build output

### Frontend UI Files
- `resources/js/components/modals/EmbedCodeModal.vue` (new)
- `resources/js/views/gallery-panels/Album.vue` (modified)

## Testing

### Run Unit Tests
```bash
npm run test:embed
```

### Run Integration Tests
```bash
php artisan test --filter Embed
```

### Manual Testing
See IMPLEMENTATION.md "Manual Testing Checklist" for comprehensive test scenarios.

## Documentation

### For End Users
- Location: `public/embed/README.md`
- Contents: How to use the embed feature, configuration options, examples

### For Developers
- This directory (`/.ai/embeds/`)
- Contents: Specification, implementation plan, architecture

## Success Metrics

The feature will be considered successful when:

1. ✅ Users can generate embed code in < 2 minutes
2. ✅ Widget loads in < 2 seconds
3. ✅ Bundle size < 100KB gzipped
4. ✅ All 5 layout types work identically to Lychee native
5. ✅ WCAG 2.1 Level AA compliance
6. ✅ Works on all target browsers
7. ✅ Zero security vulnerabilities
8. ✅ < 5% error rate in production

## Next Steps

1. Review and approve SPECIFICATION.md
2. Review and approve IMPLEMENTATION.md
3. Set up project tracking (issues, milestones)
4. Begin Week 1: Backend implementation
5. Set up continuous testing during development
6. Plan deployment strategy

## Questions or Feedback

For questions about this feature:
- **What it does**: See SPECIFICATION.md
- **How to build it**: See IMPLEMENTATION.md
- **Technical research**: See initial planning task output
- **Current status**: Check project issues and milestones

## Related Resources

### Lychee Documentation
- Layout system: `resources/js/layouts/`
- Photo components: `resources/js/components/gallery/albumModule/thumbs/`
- API resources: `app/Http/Resources/`

### External Libraries
- [justified-layout](https://github.com/flickr/justified-layout) - Flickr's layout library
- [Vite](https://vitejs.dev/) - Build tool
- [TypeScript](https://www.typescriptlang.org/) - Language

### Competitive Examples
- Flickr embed feature
- Google Photos embed
- SmugMug embed options

---

**Document Status**: ✅ Complete and ready for review
**Last Updated**: 2025-10-28
**Version**: 1.0
