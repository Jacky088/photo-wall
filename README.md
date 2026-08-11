# WP Photo Wall

A minimalist photo wall WordPress plugin with powerful admin management and optimized frontend performance.

## Preview

![photowall preview](./screenshot-preview-pc-photowall.jpg)

## Features

- **Minimalist design**: clean, modern UI.
- **Powerful admin management**:
    - Add local images from the WordPress Media Library.
    - Add external link images (CDN URLs with query params supported).
    - **Group management**: create multiple groups, move images via drag & drop or batch selection.
    - **Group ordering**: drag the handle on the left of a group title to reorder groups on the frontend.
    - **Batch operations**: batch move groups, batch remove from wall, permanent delete from Media Library.
- **Performance optimized**:
    - **Admin**: thumbnails preloaded once on the PHP side, eliminating N+1 AJAX requests; batched DOM updates.
    - **Frontend**:
        - **Async decode (`decoding="async"`)** to avoid blocking the main thread.
        - **Content visibility (`content-visibility`)**: only renders images in the viewport, greatly reducing memory usage.
        - **Lazy loading (`loading="lazy"`)** for faster first paint.
        - **Scroll throttling**: hover animations are disabled while scrolling for smoother mobile experience.
        - **Fade-in animation**: staggered fade-in on image load.
- **Responsive layout**: adapts to desktop, tablet, and mobile.
- **Built-in Lightbox**: lightweight lightbox with keyboard navigation and focus trap.
- **Top Banner Carousel**: an automatic carousel of manually selected images at the top of each `[photo_wall]`, with local Media Library and external link support, configurable interval, slide transition, hover pause, and click-to-lightbox.

## Project Structure

```
photo-wall/
├── wp-photo-wall.php          # Main entry file (lightweight loader)
├── includes/
│   ├── i18n.php               # i18n translation definitions
│   ├── data.php               # Data operations and validation
│   ├── ajax.php               # AJAX request handling
│   ├── frontend.php           # Frontend rendering and shortcode
│   └── slides.php             # Top carousel data layer and rendering
├── admin/
│   ├── admin-page.php         # Admin page template
│   ├── admin-slides.php       # Admin carousel management template
│   ├── admin-script.js        # Admin interaction logic
│   └── admin-style.css        # Admin styles
├── public/
│   ├── shortcode.php          # Shortcode output template
│   ├── gallery-script.js      # Frontend interaction (lightbox, infinite scroll)
│   ├── gallery-style.css      # Frontend styles
│   ├── slider-script.js       # Frontend carousel interaction (autoplay, slide, hover pause)
│   └── slider-style.css       # Frontend carousel styles
├── uninstall.php              # Uninstall cleanup
└── README.md
```

## Installation

1. Download the plugin zip (`photo-wall-2.1.0.zip`).
2. In WordPress admin → **Plugins** → **Add New**.
3. Click **Upload Plugin**, select the zip file and install.
4. Activate the plugin.

## Usage

### 1. Add Images

Find the **"Photo Wall"** menu in the WordPress admin sidebar.

- Click **"Select/Upload Photos"** to choose from the Media Library.
- Click **"Add External Link"** to enter an image URL.
- Newly added images default to the "Ungrouped" area.

### 2. Group Management

- Enter a name at the top of the photo management area and click **"Add Group"**.
- **Drag images**: drag an image directly to the target group.
- **Batch move**: check images → click **"Move Selected"** → pick the target group → confirm.
- **Reorder groups**: drag the handle (≡) on the left of a group title.
- All changes take effect after clicking **"Save Changes"**.

### 3. Display on a Page

Use the shortcode in any page or post:

```
[photo_wall]
```

> Images must be moved into a created group to appear on the frontend; "Ungrouped" is a backend staging area and is not publicly displayed.

Multiple `[photo_wall]` instances can be placed on the same page; each instance has independent pagination and lightbox state. Each instance's top carousel is also independent (shares the same selection config but plays independently).

### 4. Top Banner Carousel

In the **"Photo Wall"** admin page, find the **"Top Banner Carousel"** section at the top:

- Check **"Enable Carousel"** to show an auto-playing carousel at the top of each `[photo_wall]`.
- Click **"Add from Media Library"** to pick local images, or **"Add External Link"** to enter an image URL.
- Drag the handle on the left of an image to reorder the carousel.
- Set the **"Interval"** (seconds) and the **"Open Lightbox on Click"** toggle.
- All changes take effect after clicking **"Save Changes"**.

The carousel supports slide transition, hover/focus pause, touch swipe, and reuses the existing lightbox for full-size viewing.

### 5. Frontend Features

- Hovering an image gives a subtle lift effect.
- Click an image to open the fullscreen lightbox.
- Scrolling to the bottom auto-loads more (infinite scroll).
- The lightbox supports keyboard navigation (←→ to switch, Esc to close), focus cycling, and focus restoration.

### 6. Settings

- **Enable Lightbox**: turn off the built-in lightbox if your theme already provides one.
- **Download Button Link**: configure an external drive download URL shown on the frontend.
- **Top Banner Carousel**: enable/disable carousel, set autoplay interval, click-to-lightbox.

## Requirements

- WordPress 6.0+
- PHP 7.4+

## Changelog

### 2.1.0
- **New**: Top banner carousel (manual selection + autoplay)
    - Local Media Library images and external link images supported
    - Each `[photo_wall]` instance displays and plays independently
    - Slide transition, hover/focus pause, touch swipe
    - Click to open the existing lightbox for full-size view (configurable)
    - Data stored independently (separate option), no impact on the original photo wall structure or layout
    - Images auto cropped and filled (`object-fit: cover`) so different sizes never leave blank gaps

### 2.0.3
- **Frontend**: staggered fade-in animation on image load
- **Frontend**: redesigned group titles with gradient accent line and more spacing
- **Fix**: unsaved-changes notice falsely triggered on initial page load
- **Improvement**: unsaved-changes notice moved to a prominent top notification bar
- **Refactor**: split main file into modular structure (i18n / data / ajax / frontend)
- **Performance**: thumbnails preloaded once on the PHP side, eliminating N+1 AJAX requests

## License

GPL v2 or later
