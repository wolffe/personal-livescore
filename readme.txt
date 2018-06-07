=== Personal Livescore ===
Contributors: butterflymedia
Tags: livescore, match, score, goal, goalscorer
License: GPLv3
Requires at least: 4.6
Tested up to: 4.9.6
Stable tag: 3.1.0

== Description ==

This plugin allows the administrator to run and maintain a livescore system without the need to sign up for various web services or feeds. The plugin is completely standalone.

== Installation ==

1. Upload the 'personal-livescore' folder to your '/wp-content/plugins/' directory
2. Activate the plugin via the Plugins menu in WordPress
3. Create and publish a new page/post and add this shortcode: [livescore]
4. A new Livescore menu will appear in WordPress with options and general help

== Changelog ==

= 3.1.0 =
* UPDATE: Updated admin section to be more intuitive
* UPDATE: Added labels to buttons to improve user experience
* UPDATE: Updated WordPress compatibility
* UPDATE: Updated license

= 3.0.2 =
* UPDATE: Updated score fields from being capped at 99 (new value is 999)
* UPDATE: Updated WordPress compatibility

= 3.0.1 =
* FIX: Fixed readme.txt plugin details
* UPDATE: Updated embeddable CSS
* UPDATE: Improved typography and removed several inline styles

= 3.0.0 =
* FIX: Fixed integer sanitization
* FIX: Fixed CSS box sizing
* FIX: Fixed WordPress admin styles
* UPDATE: Updated jQuery version for the embed widget
* UPDATE: Removed images and replaced them widh Dashicons
* UPDATE: Added dismissible notices

= 2.6 =
* IMPROVEMENT: Removed local jQuery file and enqueued CDN
* IMPROVEMENT: Loaded wp-load.php instead of wp-config.php
* IMPROVEMENT: Better wp-load.php localization
* IMPROVEMENT: Forced cast-to-integer for refresh interval
* IMPROVEMENT: Better typography for the embedded version of the livescore
* IMPROVEMENT: Removed unused CSS stylesheets as the current one can be changed

= 2.5 =
* Removed custom fonts
* Made backend section more responsive

= 2.4.2 =
* Added check for empty parameter
* Added WordPress 4.0 compatibility
* Fixed duplicate name for wp_head() call
* Fixed livescore issue with empty 'rel' GET

= 2.4.1 =
* Fixed wrong package

= 2.4 =
* Fixed $wpdb queries
* Fixed weird paragraph issues when PHP code added a line break
* Added single ID parameter for shortcode
* Updated WordPress compatibility
* Updated HTML5 code for the embeddable version

= 2.3 =
* Added goal scorers and goal times for each match

= 2.2 =
* Added new status - 3 - for upcoming, but hidden, matches

= 2.1 =
* Added date and time picker for older browsers
* Added contextual help (legend)
* Added removal option for archived matches/games

= 2.0 =
* First public release
