# Embeddable Photo Album Widget - Feature Specification

## Document Information
- **Version**: 1.0
- **Date**: 2025-10-28
- **Status**: Approved

## Executive Summary

This specification describes a new feature that allows Lychee users to embed their photo content on external websites using a simple JavaScript widget. Users can embed either specific photo albums or a chronological stream of all their public photos. They can copy/paste HTML code to display a beautiful, interactive photo gallery on any website, similar to how Flickr and other photo services offer embeddable galleries.

## Goals

### Primary Goals
1. Enable users to share their Lychee photo albums and photo streams on external websites
2. Provide a rich, interactive viewing experience that matches Lychee's native gallery
3. Support all of Lychee's existing gallery layout types
4. Make embedding simple for non-technical users
5. Support both album-specific and stream-based embedding modes

### Secondary Goals
1. Maintain performance with optimized loading and rendering
2. Ensure accessibility for all users
3. Support theming to match different website designs
4. Provide flexibility through configuration options

## User Stories

### Album Owner
- As a Lychee user, I want to embed my public album on my blog so visitors can view my photos without leaving my site
- As a photographer, I want to embed all my public photos as a dynamic stream on my portfolio website
- As a photographer, I want to customize the layout and appearance of the embedded gallery to match my website's design
- As a content creator, I want to easily copy/paste embed code without technical knowledge
- As a blogger, I want to embed my latest photos automatically without updating the embed code

### Website Visitor
- As a website visitor, I want to view photos in a beautiful gallery without being redirected
- As a mobile user, I want the embedded gallery to work well on my device
- As a keyboard user, I want to navigate the gallery using keyboard shortcuts

## Feature Description

### Embed Modes

The widget supports two embedding modes:

#### Album Mode
Embeds photos from a specific album:
- Shows only photos from the selected album
- Album must be public (no password, not link-only)
- Album metadata can be displayed (title, description, etc.)
- Ideal for showcasing specific collections (vacation, events, projects)

#### Stream Mode
Embeds all public photos as a chronological feed:
- Shows all photos from public albums (most recent first)
- Automatically includes new photos as they're added to public albums
- Respects the same visibility rules as RSS feeds
- NSFW filtering follows RSS settings
- Limited to a maximum number of photos (configurable)
- Ideal for portfolio sites, blogs, or dynamic photo feeds

### Embed Code Generation

#### From Album View
Users can generate embed code from any public album:

1. Open a public album in Lychee
2. Click the "Embed" button (code icon) in the album toolbar
3. Dialog opens automatically in **Album Mode**
4. Configure display options (layout, spacing, etc.)
5. Preview the embedded gallery
6. Copy the generated HTML/JavaScript code
7. Paste the code into their website

#### From Sidebar Menu
Users can generate stream embed code from the sidebar:

1. Click "Embed Photo Stream" in the left sidebar menu (below Map)
2. Dialog opens automatically in **Stream Mode**
3. Configure display options (layout, spacing, max photos, etc.)
4. Preview the embedded stream
5. Copy the generated HTML/JavaScript code
6. Paste the code into their website

### Supported Gallery Layouts

The widget supports all 5 gallery layout types:

#### 1. Square Thumbnails
- All photos displayed as perfect squares in a regular grid
- Photos cropped to fit
- Configurable square size (100-400px)
- Regular spacing and alignment

#### 2. Justified Layout (Flickr-style)
- Photos arranged in rows with consistent height
- Aspect ratios preserved
- Photos scaled to fit perfectly within rows
- Configurable target row height (200-500px)
- Uses the same `justified-layout` library as Lychee

#### 3. Masonry Layout (Pinterest-style)
- Photos arranged in columns with variable heights
- Aspect ratios preserved
- Items placed in shortest available column
- Configurable column width (200-500px)
- Organic, flowing appearance

#### 4. Grid Layout
- Regular grid with fixed column widths
- Aspect ratios preserved within columns
- Row heights synchronized across columns
- Configurable column width (150-400px)
- Balanced between uniformity and aspect ratios

#### 5. Filmstrip Layout
- Single large photo display
- Horizontal thumbnail strip below for navigation
- Active thumbnail highlighted
- Previous/Next navigation
- Configurable thumbnail height (60-150px)
- Optional autoplay mode

### Lightbox Viewer

Clicking any photo opens a full-screen lightbox with:

- Large photo display (medium or medium2x variant)
- Previous/Next navigation (arrows and keyboard)
- Close button and click-outside-to-close
- Photo counter (e.g., "5 of 42")
- Photo information panel:
	- Title
	- Description/caption
	- EXIF data (camera, lens, settings, date)
- Thumbnail strip at bottom (optional)
- Swipe gestures for mobile
- Smooth transitions

### Album Metadata Display

The widget can optionally display album information:

- Album title
- Album description
- Photo count
- Copyright notice
- License information
- Link back to full Lychee gallery

### Responsive Design

The embedded gallery automatically adapts to:

- Different screen sizes (mobile, tablet, desktop)
- Parent container width
- Orientation changes
- Dynamic column calculation based on available width
- Optimized image sizes for each device

### Configuration Options

Users can customize the embedded gallery:

#### Mode Selection (Automatic)
- **Album Mode**: Automatically selected when opened from an album
- **Stream Mode**: Automatically selected when opened from sidebar menu
- Mode cannot be changed after dialog opens (determined by context)

#### Layout Options
- Layout type (square, justified, masonry, grid, filmstrip)
- Thumbnail sizes (varies by layout)
- Gap between items (0-50px)
- Target row height (100-1000px) for justified/filmstrip layouts
- Target column width (100-800px) for grid/masonry/square layouts

#### Display Options
- Show/hide album information (album mode only)
- Show/hide photo captions
- Show/hide EXIF data
- Header placement (top, bottom, none)

#### Sort Options
- Photo sort order: "newest first" (default) or "oldest first"
- Applies to both album and stream modes
- Sorted by photo taken date (if available) or upload date

#### Stream-Specific Options
- Maximum photos to display (6, 12, 18, 30, 60, 90, 120, 180, 300, 500, or "none" for all, max: 500)
- Automatically updates when new photos are added to public albums

#### Dimensions
- Responsive (100% width) or fixed dimensions
- Height: auto or specified

## Access Control

### Album Mode Requirements
Only **public albums** can be embedded:

- Album must have public access permissions
- No password protection
- Not link-required only

### Stream Mode Requirements
Only **photos from public albums** appear in streams:

- Photos must be in albums marked as public
- No password protection on albums
- Not link-required only
- Respects the same visibility rules as RSS feeds
- NSFW albums may be filtered based on RSS settings

### Security Considerations
- Signed URLs for photos (with appropriate expiration)
- CORS properly configured for cross-origin access
- No authentication required for public content
- No exposure of private album data
- Rate limiting: Stream mode (30 req/min) vs Album mode (120 req/min)
- Caching: Stream mode (15 min) vs Album mode (5 min)

## Technical Requirements

### Browser Support
- Chrome (latest, -1, -2 versions)
- Firefox (latest, -1)
- Safari (latest, -1)
- Edge (latest)
- Mobile Safari (iOS)
- Chrome Mobile (Android)

### Performance Targets
- Widget bundle size: < 100KB gzipped
- Initial load time: < 2 seconds
- Image loading: Progressive (placeholder → thumbnail → full)
- Lazy loading for off-screen images
- Smooth animations (60fps)

### Accessibility Requirements
- WCAG 2.1 Level AA compliance
- Keyboard navigation support
- Screen reader compatibility
- ARIA labels on interactive elements
- Focus management
- Color contrast compliance
- Reduced motion support

### Integration Requirements
- No conflicts with parent page CSS/JS
- Namespaced CSS classes
- Self-contained JavaScript
- Graceful degradation
- Clear error messages

## User Interface

### Embed Code Generator Modal

The modal for generating embed code includes:

**Layout Section**
- Radio buttons for layout type selection
- Visual preview icons for each layout

**Size Configuration** (dynamic based on layout)
- Sliders for thumbnail/column sizes
- Real-time preview updates
- Pixel value display

**Display Options**
- Checkboxes for metadata, captions, EXIF
- Theme selector (light/dark/auto)
- Gap size slider

**Dimensions**
- Radio buttons: Responsive or Fixed
- Width/height inputs (if fixed)

**Preview**
- Live preview iframe showing the embedded gallery
- Reflects all configuration changes in real-time

**Generated Code**
- Syntax-highlighted HTML/JavaScript snippet
- Copy to clipboard button
- Instructions for embedding

### Embedded Gallery UI

**Photo Grid/Layout**
- Thumbnails with hover effect
- Loading placeholders
- Smooth fade-in animations

**Filmstrip UI**
- Large photo display area (70-80% height)
- Thumbnail strip (20-30% height)
- Navigation arrows
- Active thumbnail highlighted
- Scroll indicators

**Lightbox UI**
- Full-screen overlay
- Large photo centered
- Previous/Next arrows (sides)
- Close button (top-right)
- Photo counter (top-left)
- Info panel (bottom or side)
- Thumbnail strip (bottom, optional)

## API Specifications

### Album Embed API Endpoint

**Endpoint**: `GET /api/v2/Embed/{albumId}`

**Query Parameters**:
- `sort` (optional): Sort order for photos. Values: `desc` (newest first, default) or `asc` (oldest first)

**Request**:
```http
GET /api/v2/Embed/abc123?sort=desc
Host: photos.example.com
Origin: https://blog.example.com
```

**Response** (Success):
```json
{
	"album": {
		"id": "abc123",
		"title": "Vacation 2024",
		"description": "Summer trip to the mountains",
		"photo_count": 42,
		"copyright": "© 2024 John Doe",
		"license": "CC BY-NC 4.0"
	},
	"photos": [
		{
			"id": "xyz789",
			"title": "Mountain Sunrise",
			"description": "Beautiful sunrise over the peaks",
			"size_variants": {
				"placeholder": {
					"url": "https://photos.example.com/image/...",
					"width": 10,
					"height": 10
				},
				"thumb": {
					"url": "https://photos.example.com/image/...",
					"width": 200,
					"height": 133
				},
				"thumb2x": {
					"url": "https://photos.example.com/image/...",
					"width": 400,
					"height": 266
				},
				"small": {
					"url": "https://photos.example.com/image/...",
					"width": 720,
					"height": 480
				},
				"small2x": {
					"url": "https://photos.example.com/image/...",
					"width": 1440,
					"height": 960
				},
				"medium": {
					"url": "https://photos.example.com/image/...",
					"width": 1920,
					"height": 1280
				},
				"medium2x": {
					"url": "https://photos.example.com/image/...",
					"width": 3840,
					"height": 2560
				},
				"original": {
					"width": 6000,
					"height": 4000
				}
			},
			"exif": {
				"make": "Canon",
				"model": "EOS R5",
				"lens": "RF 24-70mm F2.8 L IS USM",
				"iso": "400",
				"aperture": "f/2.8",
				"shutter": "1/250s",
				"focal": "50mm",
				"taken_at": "2024-07-15T06:30:00Z"
			}
		}
	]
}
```

**Response** (Album Not Public):
```json
{
	"error": "Album is not publicly accessible",
	"code": 403
}
```

**Response** (Album Not Found):
```json
{
	"error": "Album not found",
	"code": 404
}
```

### Stream Embed API Endpoint

**Endpoint**: `GET /api/v2/Embed/stream`

**Query Parameters**:
- `limit` (optional): Maximum number of photos to return (default: 18, max: 500)
- `sort` (optional): Sort order for photos. Values: `desc` (newest first, default) or `asc` (oldest first)

**Request**:
```http
GET /api/v2/Embed/stream?limit=30&sort=desc
Host: photos.example.com
Origin: https://blog.example.com
```

**Response** (Success):
```json
{
	"photos": [
		{
			"id": "xyz789",
			"title": "Mountain Sunrise",
			"description": "Beautiful sunrise over the peaks",
			"taken_at": "2024-07-15T06:30:00Z",
			"size_variants": {
				"placeholder": {
					"url": "https://photos.example.com/image/...",
					"width": 10,
					"height": 10
				},
				"thumb": {
					"url": "https://photos.example.com/image/...",
					"width": 200,
					"height": 133
				},
				"thumb2x": {
					"url": "https://photos.example.com/image/...",
					"width": 400,
					"height": 266
				},
				"small": {
					"url": "https://photos.example.com/image/...",
					"width": 720,
					"height": 480
				},
				"small2x": {
					"url": "https://photos.example.com/image/...",
					"width": 1440,
					"height": 960
				},
				"medium": {
					"url": "https://photos.example.com/image/...",
					"width": 1920,
					"height": 1280
				},
				"medium2x": {
					"url": "https://photos.example.com/image/...",
					"width": 3840,
					"height": 2560
				},
				"original": {
					"width": 6000,
					"height": 4000
				}
			},
			"exif": {
				"make": "Canon",
				"model": "EOS R5",
				"lens": "RF 24-70mm F2.8 L IS USM",
				"iso": "400",
				"aperture": "f/2.8",
				"shutter": "1/250s",
				"focal": "50mm",
				"taken_at": "2024-07-15T06:30:00Z"
			}
		}
	]
}
```

**Response** (No Public Photos):
```json
{
	"photos": []
}
```

### JavaScript Widget API

**Album Mode Initialization**:
```javascript
new LycheeEmbed.createLycheeEmbed(
	document.getElementById('lychee-embed-container'),
	{
		// Required
		apiUrl: 'https://photos.example.com',
		mode: 'album',
		albumId: 'abc123',

		// Layout options
		layout: 'justified', // 'square' | 'justified' | 'masonry' | 'grid' | 'filmstrip'
		spacing: 8,
		targetRowHeight: 200,
		targetColumnWidth: 300,

		// Display options
		showTitle: true,
		showDescription: true,
		showCaptions: true,
		showExif: true,
		headerPlacement: 'top', // 'top' | 'bottom' | 'none'

		// Sort options
		sortOrder: 'desc', // 'desc' (newest first) | 'asc' (oldest first)

		// Callbacks
		onLoad: function() {
			console.log('Gallery loaded');
		},
		onError: function(error) {
			console.error('Gallery error:', error);
		}
	}
);
```

**Stream Mode Initialization**:
```javascript
new LycheeEmbed.createLycheeEmbed(
	document.getElementById('lychee-stream-container'),
	{
		// Required
		apiUrl: 'https://photos.example.com',
		mode: 'stream',

		// Layout options
		layout: 'justified',
		spacing: 8,
		targetRowHeight: 200,
		targetColumnWidth: 300,
		maxPhotos: 30, // or 'none' for all

		// Display options
		showCaptions: true,
		showExif: true,
		headerPlacement: 'bottom',

		// Sort options
		sortOrder: 'desc', // 'desc' (newest first) | 'asc' (oldest first)

		// Callbacks
		onLoad: function() {
			console.log('Stream loaded');
		},
		onError: function(error) {
			console.error('Stream error:', error);
		}
	}
);
```

## Implementation Phases

### Phase 1: Backend Foundation
- Create Embed API endpoint
- Configure CORS for cross-origin access
- Extend photo URL expiration for embeds
- Add API routes

### Phase 2: Widget Core
- Set up widget build configuration
- Implement API client
- Create layout manager
- Implement all 5 layout types
- Add responsive calculation utilities

### Phase 3: UI Components
- Build lightbox component
- Create album header component
- Implement image loading strategy
- Add styling and themes

### Phase 4: Embed Generator
- Create embed code generator modal
- Add configuration form
- Implement live preview
- Integrate into album view

### Phase 5: Polish & Testing
- Add accessibility features
- Implement error handling
- Cross-browser testing
- Performance optimization
- Documentation

## Success Metrics

### Usability
- Users can generate embed code in < 2 minutes
- Non-technical users can successfully embed galleries
- Customization options meet 80% of user needs

### Performance
- Widget loads in < 2 seconds
- Bundle size < 100KB gzipped
- No performance impact on parent page
- Smooth animations (60fps)

### Quality
- Zero security vulnerabilities
- WCAG 2.1 Level AA compliant
- Works on all target browsers
- < 5% error rate in production

## Future Enhancements

Potential features for future versions:

- Password-protected album support with password prompt
- Video support in galleries
- Share buttons for individual photos
- Download button (if album permissions allow)
- Custom CSS injection for advanced customization
- Album selection (multiple albums in one embed)
- Photo filtering/sorting options
- Full-screen slideshow mode
- Social media sharing integration

## Appendices

### Appendix A: Layout Algorithms

See `IMPLEMENTATION.md` for detailed layout algorithms and calculations.

### Appendix B: Lychee Layout Research

Comprehensive research findings are documented in the initial planning phase, including:
- Existing layout implementations in Lychee
- Configuration settings and defaults
- Size variant selection logic
- Responsive calculation formulas

### Appendix C: Competitive Analysis

Similar features in other photo services:
- **Flickr**: Offers iframe-based embeds with limited customization
- **Google Photos**: Provides embeddable albums with fixed layouts
- **SmugMug**: Advanced embed options with extensive customization
- **500px**: Simple embed with lightbox viewer
- **Unsplash**: Individual photo embeds, not full galleries

Lychee's implementation differentiates by:
- Full layout type support (5 options)
- JavaScript widget approach for better integration
- Comprehensive customization options
- Open-source and self-hosted