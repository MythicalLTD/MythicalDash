# v3-remastered 1.1

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
