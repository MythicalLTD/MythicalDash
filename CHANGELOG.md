# v3-remastered 1.1

# Bugs Fixed.

- BUG: Fixed the bridge plugin saving path being null!
- BUG: Fixed `Undefined array key "author_name"`
- BUG: Fixed `PHP Fatal error:  Uncaught TypeError: MythicalDash\Chat\User\User::getTokenFromUUID(): Argument #1 ($uuid) must be of type string, null given, called in /var/www/mythicaldash-v3/backend/app/Api/User/Auth/Register.php on line 160`
- BUG: Fixed servers not being deleted on account deletion (Admin area)
- BUG: Removed caches (Added stupid delays/Permission errors & More)
- BUG: IP Defaults to 127.0.0.1 instead of trying more way :>

# Futures Added: 

- Feature: Major performance optimizations implemented
- Feature: Complete UI rebranding for a fresh look
- Feature: Documented how to change the seo tags!
- Feature: Added UI Customizer for personalized user experience
- Feature: Preloader is on CSR instead of SSR now!
- Feature: New Terms of service page design
- Feature: New Privacy policy page design
- Feature: Accounts now require to accept the terms and privacy policy!
- Feature: Support center now only shows if the instance has a discord server!
- Feature: Updated the ticket list page component!
- Feature: Added terms requirement on server creation page!
- Feature: Google ads support (GADS)
- Feature: Ability to delete accounts (self deletion)
- Feature: Ability to change resources view!
- Feature: First user will always get admin :)!
- Feature: New user lookup page!
- Feature: Complete rewrite of the permission system!

# Futures Removed: 

- Feature: Removed the test email from admin area :)
- Feature: Ability to use external links for privacy policy and terms of service!
- Feature: Some security mitigations were put on reflect instead of strict!

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
