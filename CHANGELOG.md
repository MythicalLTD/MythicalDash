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
