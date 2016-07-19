=== Easy Theme and Plugin Upgrades ===
Contributors: chrisjean
Tags: plugin, theme, upgrade, update, upload
Requires at least: 4.4
Tested up to: 4.5.3
Stable tag: 1.0.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily upgrade your themes and plugins using zip files without removing the theme or plugin first.

== Description ==

WordPress has a built-in feature to install themes and plugins by supplying a zip file. Unfortunately, you cannot upgrade a theme or plugin using the same process. Instead, WordPress will say "destination already exists" when trying to upgrade using a zip file and will fail to upgrade the theme or plugin.

Easy Theme and Plugin Upgrades fixes this limitation in WordPress. When active, you get an option to allow the theme or plugin to be upgraded.

While upgrading, a backup copy of the old theme or plugin is first created. This allows you to install the old version in case of problems with the new version.

== Frequently Asked Questions ==

= How do I upgrade a theme? =

1. Download the latest zip file for your theme.
1. Log into your WordPress site.
1. Go to Appearance > Themes.
1. Click the "Add New" button at the top of the page.
1. Click the "Upload Theme" button at the top of the page.
1. Click the file browse button to select your theme zip file. The button's text varies by browser. It typically says "Browse...", "Choose File", or "Choose...".
1. Select the zip file with the new theme version to install.
1. Select "Yes" from the "Upgrade existing theme?" option.
1. Click the "Install Now" button.

= How do I upgrade a plugin? =

1. Download the latest zip file for your plugin.
1. Log into your WordPress site.
1. Go to Plugins > Add New and click the Upload tab at the top of the page.
1. Select the zip file with the new plugin version to install.
1. Select "Yes" from the "Upgrade existing plugin?" option.
1. Click the "Install Now" button.

== Installation ==

1. Download and unzip the latest release zip file
1. Upload the entire easy-theme-and-plugin-upgrades directory to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 1.0.6 =
 * Bug Fix: Fixed updates not working with some formats of zip files. Thanks to the team at [Kaira](https://kairaweb.com/) for helping solve this issue.

= 1.0.5 =
 * Compatibility Fix: Added support for PHP 7.

= 1.0.4 =
 * Enhancement: Updated instructions on how to upgrade themes.

= 1.0.3 =
 * Compatibility Fix: Added compatibility for theme upgrades in WordPress 3.9.

= 1.0.2 =
 * Bug Fix: Removed a stray &lt;i&gt; tag in the Install Plugins screen that caused problems with installing plugins on WPEngine sites.

= 1.0.1 =
 * Bug Fix: Fixed an issue with the "The site is being updated and will be back in a few minutes" message showing on the frontend of the site for a few minutes after an upgrade. This only happened on multisite networks.

= 1.0.0 =
 * Initial release version

== Upgrade Notice ==

= 1.0.6 =
Version 1.0.6 contains a bug fix that fixes a zip compatibility issue that many users have reported.
