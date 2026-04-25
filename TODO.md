# UI/UX 2025 Revamp TODO

- [x] Rewrite `resources/css/app.css` completely (glassmorphism, 3D tilt, magnetic buttons, complete component styles)
- [x] Update `resources/views/layouts/app.blade.php` (custom cursor DOM + vanilla JS for cursor, tilt, magnetic effects)
- [x] Update `resources/views/books/index.blade.php` (add `.tilt-layer` wrappers to cards)

## Completed

1. **CSS (`resources/css/app.css`)**: Complete rewrite with glassmorphism sidebar/navbar, organic animated gradient blobs, bold 2025 typography with entrance animations, glassmorphic search inputs, 3D tilt card styles with `.tilt-layer` depth layers, ripple/magnetic button effects, complete form/detail/alert/empty-state/pagination component styles, and SPA page transition animations.

2. **Layout (`resources/views/layouts/app.blade.php`)**: Added custom cursor DOM element. Injected three vanilla JS modules:
   - Custom cursor tracking with lerp smoothing and hover state expansion
   - 3D card tilt effect with Z-axis depth translation on `.tilt-layer` children
   - Magnetic button effect with subtle pull-toward-cursor behavior
   All modules auto-reinitialize via `spaContentUpdated` event after every SPA fetch-based page swap. Fixed `updateActiveLink` to resolve relative href URLs via `new URL()` before path comparison.

3. **Books Index (`resources/views/books/index.blade.php`)**: Wrapped `.item-header`, `.item-body`, and `.item-actions` inside `.tilt-layer` div so the 3D parallax depth effect works correctly.
