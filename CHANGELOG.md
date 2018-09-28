CHANGELOG
=========

1.2
---

 * Ability to perform sorting in Home controller
 * Updated `getMostlyViewed()` in `SiteService`. Since now it can filter by category IDs, return entities in random order, and define minival view count to be taken in account
 * Posts that have a future date won't be displayed since now. They will be displayed when their date comes
 * Ability to mark posts as front. That means they are visible in their categories, but not on home page
 * Added `getSequential()` in `SiteService` that can return previous and next post starting from provided post ID
 * Support complete internalization
 * Removed menu widget
 * Ability to attach related posts
 * Better interface with tabs in forms
 * Added shortcut in administration panel
 * Added `getRecent()` in `SiteService`
 * Added getCategories() in `SiteService`
 * Added `Home` controller
 * Switched to column grid layout in main module page
 * In administration panel, replaced text input with textarea (keywords)
 * Fixed issue with quote escaping
 * Changed the way of storing configuration data. Since now its stored in the database
 * Added `name` attribute for posts and categories
 * Added missing `$category` variable for category templates
 * Added missing `getAllByCategoryId()` in the site service
 * Added `getImageUrl()` shortcut in `PostEntity`
 * Added support for table prefix
 * Added extra buttons in category form
 * Changed default sorting order to DESC when displaying categories
 * Fixed issue with saving cover sizes in configuration
 * Changed module icon
 * Improved internal structure
 * Added ability to fetch mostly viewed posts. Now users can simply call `getMostlyViewed()` on `$news` service to get a collection of entities.

1.1
---

 * Improved internals
 * Added cover uploading support

1.0
---

 * First public version