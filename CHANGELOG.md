CHANGELOG
=========

1.2
---

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