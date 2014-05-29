=== Easy Theme and Plugin Upgrades ===
Contributors: chrisjean
Tags: upload, plugins, themes, upgrade
Requires at least: 3.0
Tested up to: 3.9.1
Stable tag: 1.0.4

Easily upgrade your themes and plugins using zip files without removing the theme or plugin first.

== Description ==

__Easy Theme and Plugin Upgrades__ was created to make the life of WordPress users easier. Without this plugin, the only upgrade path you have for download zip plugins and themes is to deactivate the theme/plugin, delete it, upload, and reactivate. With this plugin, upgrading is as simple as selecting the zip file to upload, selecting "Yes" from a drop-down, and clicking "Install Now".

= Upgrading a Theme =

1. Download the latest zip file for your theme.
1. Log into your WordPress site.
1. Go to Appearance > Themes.
1. Click the "Add New" button at the top of the page.
1. Click the "Upload Theme" button at the top of the page.
1. Click the file browse button to select your theme zip file. The button's text varies by browser. It typically says "Browse...", "Choose File", or "Choose...".
1. Select the zip file with the new theme version to install.
1. Select "Yes" from the "Upgrade existing theme?" option.
1. Click the "Install Now" button.

= Upgrading a Plugin =

1. Download the latest zip file for your plugin.
1. Log into your WordPress site.
1. Go to Plugins > Add New and click the Upload tab at the top of the page.
1. Select the zip file with the new plugin version to install.
1. Select "Yes" from the "Upgrade existing plugin?" option.
1. Click "Install Now".

== Installation ==

1. Download and unzip the latest release zip file
1. Upload the entire easy-theme-and-plugin-upgrades directory to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Requirements ==

* PHP 4+
* WordPress 3.0+

== Version History ==

* 1.0.0 - 2011-07-06 - Initial release version
* 1.0.1 - 2011-09-28 - Fixed an issue with the "The site is being updated and will be back in a few minutes" message showing on the frontend of the site for a few minutes after an upgrade. This only happened on multisite networks.
* 1.0.2 - 2013-08-20 - Removed a stray &lt;i&gt; tag in the Install Plugins screen that caused problems with installing plugins on WPEngine sites.
* 1.0.3 - 2014-04-18 - Added compatibility for theme upgrades in WordPress 3.9.
