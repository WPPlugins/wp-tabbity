=== WP-Tabbity ===
Contributors: dragonflyeye
Donate link: http://holisticnetworking.net/blog/projects/wp-tabbity/
Tags: jquery, shortcode, tabs, display
Requires at least: 2.9
Tested up to: 3.2.1
Stable tag: 1.0

Allows authors to create one or more tab groups containing one or more tabs, animated by the WordPress-included version of jQuery.

== Description ==

WP-Tabbity brings the power of the jQuery UI Tabs function to WordPress authors by allowing them to use shortcodes to create one or more tab groups containing one or more tabs to any post or page. The author can just enter basic tab information for a single set of tabs or even specify CSS ID and Classes for each tab and tab group for extra customization.

The tabs are styled according to a few included jQuery Themeroller themes, but the included themes can always be modified or more could be downloaded at any time from the [jQuery Theme Roller](http://jqueryui.com/themeroller/ "Roll your own jQuery themes!"). The plugin also allows for the introduction of your own custom CSS file.

== Installation ==

To install this plugin:

1. Unzip the downloaded package and place the /wp-tabbity folder and all its contents into your wp-content/plugins folder.
2. Go to your Admin panel and activate the plugin.
3. For extra customization and further usage instructions, see [the plugin's home page](http://holisticnetworking.net/blog/projects/wp-tabbity/ "WP-Tabbity on HN.Net WordPress Plugins").

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the directory of the stable readme.txt, so in this case, `/tags/4.3/screenshot-1.png` (or jpg, jpeg, gif)
2. This is the second screen shot

== Changelog ==

= 0.1 =
* Initial public release
= 0.2 =
* Fixed issue with plugin directory: points correctly to WP_PLUGIN_URL constant, thanks to zaenal.
= 0.3 =
* Thanks again to zaenal, shortcode content should be trim()-ed before inserting into post.
= 0.4 =
* Previous version was missing styles in the SVN upload. This version corrects that.
= 0.5 =
* OOP code reorganized.
* Eliminated deprecated add_options_menu() call.
= 0.6 =
* Updating WordPress code to match the current conventions for plugin creation and menu items.
* Shortcodes are now nested, obviating the need for wp-tabbitygroup group IDs.
* All plugin code now contained in a single file.


== Basic Usage ==
(First, create your tabs:)
[wp-tabbity title="This is a tab"]
Blah, blah, blah. The content of the first tab
[/wp-tabbity]
[wp-tabbity title="This is another tab"]
Yadda, yadda, yadda. The content of the second tab.
[/wp-tabbity]
(Then, give them a home)
[wp-tabbity-group]