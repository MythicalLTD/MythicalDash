# v3.5.0-Aurora

# Bug Fixes

- BUG: Resolved an issue where password managers would autofill fields incorrectly in settings.
- BUG: Improved the Monaco editor: it now displays at the correct size for editing mail templates (thanks @Th3HunterGamer).
- BUG: Fixed an exploit in Linkvertise rewards where users could bypass the 1-hour cooldown and claim double coins by using multiple browsers. (thanks @Th3HunterGamer)
- BUG: Fixed a version mismatch problem in the CLI.
- BUG: Ensured all files are properly deleted during updates.
- BUG: Addressed issues with the mail template editor not functioning as expected.
- BUG: Corrected problems with the versioning system.
- BUG: Fixed a bug preventing users from banning other users.
- BUG: Fixed HTTPS not being indexed sometimes!
- BUG: Missing translation keys for J4R!
- BUG: Broken attachments on tickets!

# New Features

- Feature: Completely overhauled the mail system for improved reliability and flexibility.
- Feature: Introduced a robust new TimedTask system to enhance cron job management.
- Feature: Added a nice popup to show the timed tasks status!
- Feature: Discord authentication logic was reworked. Thanks @Th3HunterGamer for the cleanup!
- Feature: New mail templates for the dashboard!
- Feature: Support for latest php version! 
- Feature: Complete redesign of critical admin pages (Users, Servers, Tickets) with enhanced pagination and performance optimizations to handle thousands of items seamlessly!
- Feature: Comprehensive mass email system with template support and queue management for efficient bulk communications!
- Feature: Enhanced admin interface with modern visual styling and improved user experience!
- Feature: Admin interface now shows the change logs!

# Features Removed

- Removed: Removed the custom code editor to boost overall performance.

# v3.4.0-Nexus

# Bugs Fixed:

- BUG: Fixed servers being deleted in the database before removal in the Pterodactyl panel  
- BUG: Fixed bug allowing unlimited database slots and backups  
- BUG: Fixed invalid redeem code issue  
- BUG: Fixed missing translation keys  
- BUG: Prevented users from deleting core roles 
- BUG: Discord auth required auth while it being the auth sorce
- BUG: The background not being shown on login!

# Features Added:

- Feature: Added manual ID input support for location creation and editing  
- Feature: Added manual ID input support for egg creation and editing  
- Feature: Added manual ID input support for nest creation and editing  
- Feature: Server listing now supports more than 100 servers  
- Feature: Logs are now handled through a 3rd-party API  
- Feature: Safewall to prevent users from running the dash with no reverse proxy!
- Feature: Add docker support to box mythicaldash!
- Feature: Moved to MIT license!

# Features Removed:

- Removed: License system  
- Removed: Requirement for `excimer`  
- Removed: All telemetry from MythicalDash  
- Removed: Usage of the dash on a local port and host!

# Breaking Changes:

- None – all changes are backward compatible  
- Existing API endpoints remain unchanged  
- Database schema remains unaffected  

# v3.3.2-Nexus

# Bugs Fixed:

- BUG: Fixed widespread race condition vulnerabilities across the entire credit management system
- BUG: Fixed server editing logic errors
- BUG: Fixed database update requirements for server modifications
- BUG: Fixed API route not found errors on server deletion
- BUG: Fixed permission handling issues that could cause null values
- BUG: BG Will not show on login only after refresh!
- BUG: Timeout on db requests!

# Features Added:

- Feature: Implemented atomic operations for all credit-related transactions
- Feature: Database transactions with row-level locking prevent concurrent modifications
- Feature: Enhanced error handling for failed atomic operations with proper logging
- Feature: Improved transaction rollback handling
- Feature: Better user feedback for failed operations
- Feature: Enhanced error logging and debugging capabilities
- Feature: Implemented a way to change the default store values for what the user gets!
- Feature: New dev docs on how the codebase works!
- Feature: New makeadmin command ui!
- Feature: New users command so you can edit users via cli!
- Feature: Log files get created on first app boot now!
- Feature: Support for an upgrade command built in!

# Features Removed:

- ZeroTrust has been removed!

# Breaking Changes:

- None - All changes are backward compatible
- Existing API endpoints maintain the same interface
- Database schema remains unchanged


# v3.3.1-Nexus

## Bug Fixes

- Fixed a bug where users can go over their limits!
- Fixed null type check on discord login
- Fixed a dumb issues where croncheck will take backups for some reason.
- Pteordactyl responses were not parased on some endpoints!
- Broken At-a-Glance Analytics stats with ram/disk
- Cache pterodactyl info on configure!
- Fix small problems with the license server!
- Ability to suspend servers from users edit page!
- Banned users now get their servers suspended/unsuspended!

## Enhancements

- Added a permission to support vpn check bypass!
- Added a permission to support alting bypass!
- Added error messages for discord/github auth!
- Made it easy for noobs to build the dash!
- Admin area now checks for futures if they are enabled!
- You can now change the bg of auth/error pages!

## Breaking Changes


# v3.3.0-Nexus 

## Bug Fixes

- Fixed null server reference issues
- Fixed environment values returning null
- Fixed Redis dependency startup failure
- Fixed Pterodactyl API null values
- Fixed file permission issues
- Fixed server update dependencies
- Fixed version handling
- Fixed profile dropdown UI
- Fixed disabled profiles display
- Fixed NaN form field errors
- Fixed timezone settings
- Removed unnecessary `defineEmits` imports
- Fixed null login values during authentication
- Fixed plugin settings variable conflicts
- Rewrote GitHub and Discord authentication
- Fixed user data corruption in admin area
- Fixed missing translations keys

## Enhancements

- Added early plugin injection support
- Added bulk settings update endpoint
- Enhanced settings reading with defaults
- Redesigned settings page
- Added HTTPS auto-prepend for image hosting
- Fixed email template URL generation
- Added server list search bar
- Improved language selector
- Added startup health checks
- Added HTTPS auto-prepend for Discord/GitHub
- Added HTTPS enforcement in admin settings
- Added daily backups support
- Fixed admin UI consistency
- Added Discord guild fetching
- Added J4R (Join for Reward) support
- Added CLI settings command
- Added CLI encrypt command
- Image hosting is now built in (No more plugin)
- Added a health check cli command for more checks!
- More search results inside the admin area!
- More Events for plugins :)
- Email verify is not required if not configured!
- Ability to report images and manage reports from admin ui!
- Ability to be able to upload images via the client ui!
- Ability to use anti adblocker (With bypass permission for roles!)
- Users page now allows you to see their servers
- Users page now allows you to see their (Discord,Github) info
- You can now change the default bg!

## Breaking Changes
- None


# v3.2.2-Nexus 

## Bug Fixes

- WebServers logs are not uploading if the file was empty!
- Fixed server slot buy
- Fixed a bug with permissions
- Server max limit config into actions.php 
- Fix duplicate settings bug!

## Enhancements
- None
## Breaking Changes
- None

# v3.2.1-Nexus

## Bug Fixes

- Fixed Pterodactyl login authentication
- Resolved server import functionality issues
- Addressed servers not loading after import process
- Corrected license server check to handle missing HTTPS protocol
- Improved panel communication handling
- Fixed Linkvertise coin reward inconsistency issue
- Added missing translation strings
- Corrected CSS typing errors
- Fixed the afk area :)

## Enhancements

- Plugin-registered commands now appear in help menu
- Added ability to modify SEO tags
- Implemented CLI commands for modifying settings values
- Added CLI functionality to read all system settings
- New documentation for plugin development

## Breaking Changes

- Removed automatic admin privileges for first user due to security concerns

# v3.2.0-Nexus

## Bug Fixes

- Fixed missing translations in ticket component
- Reverted Vite MPA (Multi-Page Application) configuration changes
- Enhanced type safety in `Register.php` to prevent null reference and type coercion errors
- Improved error handling for Discord API callbacks to prevent warnings and fatal errors
- Added session null check in `PermissionMiddleware.php` to prevent fatal errors
- Fixed user-character matching for user IDs above 100
- Removed unnecessary component rendering in background layers
- Resolved VIP-only flag initialization issues
- Fixed user deletion functionality in dashboard
- Resolved server deletion issues
- Fixed plugin settings encryption system

## Enhancements

- Implemented comprehensive validation on both frontend and backend for Pterodactyl panel inputs
- Added automatic server cleanup on renewal expiration
- Enhanced user verification logic for Pterodactyl integration
- Added server deployment status visibility for users
- Implemented server deletion capability during deployment phase
- Improved initialization command functionality
- Added command to clear license cache
- Introduced URL-based plugin installation support

## Breaking Changes

- Telemetry controls moved to plugin system (UI controls removed)
- Advanced customization features restricted to premium users

## Updates

### Frontend:

- Updated `@tailwindcss/vite` to 4.1.11
- Updated `@types/node` to 24.0.13
- Updated `@vitejs/plugin-vue-jsx` to 5.0.1
- Updated `lucide-vue-next` to 0.525.0
- Updated `oxlint` to 1.6.0
- Updated `prettier` to 3.6.2
- Updated `sweetalert2` to 11.22.2
- Updated `tailwindcss` to 4.1.11
- Updated `vite` to 7.0.4
- Updated `vite-plugin-oxlint` to 1.4.0
- Updated `vue-i18n` to 11.1.9
- Updated `vue-tsc` to 3.0.1

### Backend:

- Updated `friendsofphp/php-cs-fixer` to v3.82.2
- Updated `myclabs/deep-copy` to 1.13.3
- Updated `phpunit/phpunit` to 11.5.27
- Updated `stripe/stripe-php` to v17.4.0
- Updated `symfony/console` to v7.3.1
- Updated `symfony/yaml` to v7.3.1

# v3-remastered 1.0

## Bug Fixes

- Fixed an issue where the bridge plugin's saving path was null.
- Resolved `Undefined array key "author_name"` error.
- Addressed `PHP Fatal error:  Uncaught TypeError: MythicalDash\Chat\User\User::getTokenFromUUID(): Argument #1 ($uuid) must be of type string, null given, called in /var/www/mythicaldash-v3/backend/app/Api/User/Auth/Register.php on line 160`.
- Ensured servers are properly deleted when an account is removed (Admin area).
- Removed problematic caches that caused delays and permission errors.
- Defaulted IP to 127.0.0.1 for improved reliability.
- Corrected translation issues on server page components.
- Fixed missing translations in profile dropdown components.
- Ensured user images are deleted from the image hosting bridge upon user deletion.
- Resolved various cache-related bugs.
- Simplified Composer requirements for easier setup.
- Improved compliance with PSR-4 standards.
- Resolved `/api/user/images/embed/toggle` returning 404!
- Resolved a bug where fallback locale will not load!

## New Features

- Major performance optimizations for a faster experience.
- Complete UI rebranding for a modern look.
- Added documentation for updating SEO tags.
- Introduced a UI Customizer for personalized user experiences.
- Preloader now runs on CSR instead of SSR.
- Redesigned Terms of Service and Privacy Policy pages.
- Users must now accept Terms and Privacy Policy during registration.
- Support Center visibility now depends on the presence of a Discord server.
- Updated the ticket list page component.
- Added terms acceptance requirement to the server creation page.
- Integrated Google Ads (GADS) support.
- Enabled self-service account deletion.
- Added the ability to change resource views.
- The first registered user is now always granted admin privileges.
- Introduced a new user lookup page.
- Completely rewrote the permission system.
- Implemented a new permission-based system (*.*.*).
- Added support for VIP Nodes/Eggs (requires `userextra.vip` permission).
- Migrated to the blazing-fast OXLINT formatter.

## Features Removed

- Removed the test email feature from the admin area.
- Enabled the use of external links for Privacy Policy and Terms of Service.
- Moved some security mitigations from reflect to strict mode.

## Updates

- Updated `vite` to 7.0.0
- Updated `phpparser` to 5.5.0
- Updated `stripe/stripe-php` to v17.3.0
- Updated `phpunit/phpunit` to 11.5.24
- Updated `symfony/yaml` to 7.3.0
- Updated `nikic/php-parser` to 5.5.0
- Updated `vue` to ^3.5.17
- Updated `vue-router` to ^4.5.1
- Updated `vue-tsc` to ^2.2.10
- Updated `@vitejs/plugin-vue` to ^6.0.0
- Updated `@vitejs/plugin-vue-jsx` to ^5.0.0
- Updated `typescript` to ~5.8.3
- Updated `tailwindcss` to ^4.1.10
- Updated `@tailwindcss/vite` to ^4.1.10
- Updated `chart.js` to ^4.5.0
- Updated `date-fns` to ^4.1.0
- Updated `lucide-vue-next` to ^0.523.0
- Updated `monaco-editor` to ^0.52.2
- Updated `monaco-editor-vue3` to ^0.1.10
- Updated `pinia` to ^3.0.3
- Updated `prettier` to ^3.6.1
- Updated `qrcode` to ^1.5.4
- Updated `sweetalert2` to ^11.22.1
- Updated `vue-qrcode` to ^2.2.2
- Updated `vue-sweetalert2` to ^5.0.11
- Updated `vue-turnstile` to ^1.0.11
- Updated `web-vitals` to ^5.0.3
- Updated `@vueuse/sound` to ^2.1.3
- Updated `vue-i18n` to ^11.1.7
- Updated `@modyfi/vite-plugin-yaml` to ^1.1.1
- Updated `@tanstack/vue-table` to ^8.21.3
- Updated `eslint` to ^9.29.0

# v3-remastered 1.0.0.4

# Bugs Fixed:

- BUG: Fixed coins adding bugs!
- BUG: Port defined but not used!
- BUG: Fix data not loading on settings init if the data is not a json!
- BUG: Fix translations missing in some places!

# Features Added:

- Feature: Added image hosting support to MythicalDash!
- Feature: Frontend is now included into release!

# Features Removed:

-----

# v3-remastered 1.0.0.3

# Bugs Fixed:

- BUG: License check goes over ipv4 :(
- BUG: License check doesn't have a user agent!
- BUG: Telemetry didn't have a user agent!
- BUG: Cloud didn't have a user agent!
- BUG: Fixed a bug where delete requests awaited an json when it doesn't need to!
- BUG: Fixed the: Failed to get table row count: SQLSTATE[42S22]
- BUG: Fixed a bug where items in the admin menu will also count deleted records!
- BUG: Fixed the bug with the tables count per page!
- BUG: Fixed the stupid bug with referrals showing on register even if they are disabled!
- BUG: Fixed processed users being null
- BUG: Fixed a silly bug in the admin area for clicking the button not really working :)!


# Features Added:

- Feature: Optimized how the pterodactyl wrapper works!
- Feature: Added a debug menu (Shift+I)
- Feature: If no error codes defined into translation use the api response!
- Feature: Now you can customize the error page for (rate limited, license key, backend down)
- Feature: Sessions were reworked!
- Feature: Allow custom css and custom js!
- Feature: Support for terminal access in production for debug mode!
- Feature: Better optimization!
- Feature: Better server list :)
- Feature: Image upload!
- Feature: Meta Links (Redirects on path)
- Feature: Locations support images now!
- Feature: Eggs support images now!
- Feature: Eggs support vip only users now!
- Feature: Locations support vip only users now!
- Feature: Brand server creation page!
- Feature: Premium AntiVPN/Proxy System
- Feature: Premium AntiAlt System
- Feature: Relationships are not saved in the database anymore!
- Feature: At-a-glance analytics dashboard showing user, server, and system metrics

# Features Removed:

-----


# v3-remastered 1.0.0.2

# Bugs Fixed:

- BUG: Fixed a bug where telemetry will fail if log is bigger than 250!
- BUG: Fixed a small bug with crons running in the main thread!
- BUG: Cli was trying to set a header!
- BUG: Fixed renewals running every s
- BUG: Fixed Mail spam bugs
- BUG: Fixed a bug where social media icons are over the search bar

# Features Added:

- Feature: Plugins can now create cli commands!
- Feature: Plugins can now create cron jobs!		
- Feature: Plugin export function! (You can now export plugins)
- Feature: Plugin install function! (Now you can install plugins)
- Feature: Plugins can now use onPluginInstall and onPluginUninstall!
- Feature: Added translations for German
- Feature: Added translations for Spanish
- Feature: Added translations for Mandarin
- Feature: Added a password generator on register form.
- Feature: Added a username generator on the register form.
- Feature: Added a email suggestion on the register form
- Feature: Added new cloud system for logs
- Feature: Added new license key system
- Feature: Removed unused files
- Feature: Debug logs are not saved anymore if the app is built into production
- Feature: Better telemetry sending
- Feature: License Keys are now required to use the app
- Feature: You can now create plugins via the cli!
- Feature: Better MythicalZero logic!
- Feature: You can now take snapshots!
- Feature: You can now list snapshots!
- Feature: You can now revert from snapshots!
- Feature: You can onw delete snapshots!
- Feature: Plugins can hook into backups!
- Feature: Daily backups of the instance!
- Feature: Added a health page!
- Feature: Add a option to upload logs from health page!
- Feature: MythicalCloud (Upload,Download,List,Purge) your backups in the cloud!

# Features Removed:

- Ability to use the dashboard with no license
- Ability to change the license key in the settings
- Ability to disable telemetry for non paid people 

-----


# v3-remastered 1.0.0.1

# Bugs Fixed:

- BUG: Fixed the discord link button
- BUG: Linkvertise gives you 2k coins :)
- BUG: Can't install with .env missing
- BUG: Plugins were loaded before db connection

# Features Added:

- Feature: Added `renewal` Features 
- Feature: More settings for referrals 
- Feature: Now you can see the server build logs
- Feature: Language selector
- Feature: Added french translation thanks to @raphrapide100
- Feature: Added romanian translation thanks to @nayskutzu
- Feature: New `php mythicaldash logs` command! :)

# Features Removed:

- REMOVED: MythicalCloud Backups (TEMP)
