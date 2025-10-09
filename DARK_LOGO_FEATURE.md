# Dark Mode Logo Feature

## Overview
The application now supports separate logos for light and dark modes. When a dark mode logo is uploaded, it will automatically be displayed when the user switches to dark mode.

## Configuration

### Admin Panel
1. Navigate to **System → Settings** in the Filament admin panel
2. Under the "General Settings" section, you'll find:
   - **Site Logo**: The logo displayed in light mode
   - **Site Logo (Dark Mode)**: Optional logo for dark mode

### Behavior
- If only the regular logo is uploaded, it will be shown in both light and dark modes
- If both logos are uploaded, the app will automatically switch between them based on the user's theme preference
- If no logo is uploaded, the site title will be displayed as a text fallback

## Frontend Usage

### Using the SiteLogo Component
The `SiteLogo` component automatically handles dark mode logo switching:

```tsx
import { SiteLogo } from '@/Components/Settings/SettingsComponents';

// Basic usage
<SiteLogo />

// With custom size
<SiteLogo size="lg" />

// Without title
<SiteLogo showTitle={false} />

// With custom className
<SiteLogo className="my-custom-class" size="md" />
```

### Available Props
- `className`: Additional CSS classes (optional)
- `size`: Logo size - 'sm' | 'md' | 'lg' | 'xl' (default: 'md')
- `showTitle`: Display site title next to logo (default: true)

### Available Sizes
- `sm`: 32px height (h-8)
- `md`: 48px height (h-12)
- `lg`: 64px height (h-16)
- `xl`: 96px height (h-24)

## Technical Implementation

### Database
The dark logo setting is stored in the `settings` table:
- **Key**: `site_logo_dark`
- **Group**: `general`
- **Type**: `image`
- **Value**: File path (nullable)

### Backend (SettingsService)
```php
use App\Services\SettingsService;

$config = SettingsService::getSiteConfig();
// Returns: ['site_logo' => '...', 'site_logo_dark' => '...', ...]
```

### Frontend (useSettings Hook)
```tsx
import { useSiteBranding } from '@/hooks/useSettings';

const { title, logo, logoDark, icon } = useSiteBranding();
```

## Testing
Tests are available at `tests/Feature/Settings/DarkLogoSettingTest.php` covering:
- Setting existence
- Retrieval from settings service
- Site config inclusion
- Null/empty values handling
- Setting updates

Run tests with:
```bash
php artisan test --filter=DarkLogoSettingTest
```

## Notes
- The dark logo is optional - if not provided, only the regular logo will be shown
- Images are stored in the `storage/app/public/settings` directory
- Both logos are resized maintaining aspect ratio based on the size prop
- Tailwind's `dark:` variant is used for automatic theme-based switching
