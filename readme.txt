=== Easy Theme and Plugin Upgrades ===
Contributors: chrisjean
Tags: plugin, theme, upgrade, update, upload
Requires at least: 4.4
Tested up to: 4.6
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily upgrade your themes and plugins using zip files without removing the theme or plugin first.


== Description ==

WordPress has a built-in feature to install themes and plugins by supplying a zip file. Unfortunately, you cannot upgrade a theme or plugin using the same process. Instead, WordPress will say "destination already exists" when trying to upgrade using a zip file and will fail to upgrade the theme or plugin.

Easy Theme and Plugin Upgrades fixes this limitation in WordPress by automatically upgrading the theme or plugin if it already exists.

While upgrading, a backup copy of the old theme or plugin is first created. This allows you to install the old version in case of problems with the new version.

Attention: Version 2.0.0 changed the functionality of the plugin. You are no longer required to select "Yes" from a drop down before the theme or plugin can be upgraded. The need for an upgrade is now detected automatically. So, if you are used to the old functionality of the plugin, do not be concerned about the absence of upgrade details on the theme and plugin upload pages. Simply upload the theme or plugin as if you were installing it, and the plugin will automatically handle upgrading as needed.


== Frequently Asked Questions ==

= Why does the plugin no longer show the drop down to select "Yes"? Is the plugin broken? =

Version 2.0.0 no longer requires that a drop down is used to indicate that an upgrade is to be performed. The plugin now can determine if an upgrade is required automatically. This change not only streamlines the process for many users, it also fixed compatibility issues that some users experienced with older versions.

= How do I upgrade a theme? =

1. Download the latest zip file for your theme.
1. Log into your WordPress site.
1. Go to Appearance > Themes.
1. Click the "Add New" button at the top of the page.
1. Click the "Upload Theme" button at the top of the page.
1. Select the zip file with the new theme version to install.
1. Click the "Install Now" button.

= How do I upgrade a plugin? =

1. Download the latest zip file for your plugin.
1. Log into your WordPress site.
1. Go to Plugins > Add New.
1. Click the "Upload Plugin" button at the top of the page.
1. Select the zip file with the new plugin version to install.
1. Click the "Install Now" button.

= How do I access the backup of an old theme or plugin? =

1. Log into your WordPress site.
1. Go to Media > Library.
1. Type "backup" into the search input and press the "Enter" key.
1. Find the desired backup from the resulting list.
1. Click the title of the desired backup.
1. The URL to the backup file is listed on the right side of the page under "File URL". You can copy and paste that URL into your browser's URL bar in order to start a download.


== Installation ==

1. Download and unzip the latest release zip file
1. Upload the entire easy-theme-and-plugin-upgrades directory to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 2.0.0 =
 * Enhancement: Removed the requirement for the user to select "Yes" from the drop down in order to initiate an update. This new version does not change the appearance of the upload form. Instead, it automatically creates a backup and performs an upgrade if the supplied plugin or theme already exists.
 * Enhancement: If a zip backup file cannot be created, the old directory is renamed to a new name in order to still keep a backup.
 * Enhancement: Updated the code to use a better way of integrating the upgrade logic. This approach should greatly reduce the potential for conflicts with other code or site configurations.
 * Enhancement: The backup details now are found in the same format as the rest of the upgrade messages.

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

= 2.0.0 =
Version 2.0.0 fixes many of the site and plugin compatibility issues that people have reported and removes any need to tell the plugin to perform an upgrade.
