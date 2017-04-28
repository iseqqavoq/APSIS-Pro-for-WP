=== APSIS Pro for WP ===
Contributors: iseqqavoq
Tags: apsis, newsletter, subscription, mailing list
Requires at least: 4.0
Tested up to: 4.7.4
Stable tag: 1.0.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

APSIS Pro for WP integrates APSIS Pro with WordPress, so you can add subscription forms to APSIS Pro on your site.

== Description ==
APSIS Pro for WP integrates APSIS Pro with WordPress and makes it possible to add subscription forms to APSIS Pro on your site. You can have multiple subscription forms pointing to different mailing lists and one form can have checkboxes for multiple mailing lists. The forms can be added to your site with shortcodes and widgets.

If you have any general questions regarding this plugin, please visit WordPress forums.

For more information on integration with APSIS Pro, goto http://customers.anpdm.com/apsisproforwordpress/help.html. 

Visit APSIS’ web page at www.apsis.com.

Feel free to make pull requests for improvement on Github at https://github.com/iseqqavoq/apsis-pro-for-wp

== Installation ==
1. Upload the apsis-pro-for-wp folder to the /wp-content/plugins/ directory

2. Activate the plugin through the Plugins menu in WordPress

3. Check the APSIS Pro settings page in order to add your APSIS Pro API Key. For more information on integration with APSIS Pro, goto http://customers.anpdm.com/apsisproforwordpress/help.html

4. Insert API Key and select the website you access APSIS Pro through.

5. Click Save API Settings.

6. To add the subscription form to your site, either use the Shortcode Generator on the settings page to generate a shortcode or go to Widgets and add APSIS Pro Widget. Check off one or more mailing lists that you want to the form to use.

== Frequently Asked Questions ==
If you have any general questions regarding this plugin, please visit WordPress forums.

For more information on integration with APSIS Pro, goto http://customers.anpdm.com/apsisproforwordpress/help.html. 

Visit APSIS’ web page at www.apsis.com.

== Screenshots ==
1. Subscription form
2. Settings page
3. Widget settings

== Changelog ==
= 1.0.9 =
* Fixed bug when using form with only one mailing list.
* Fixed default text for submit button text in widgets.
* Modified description texts and translations.
* Modified readme and changelog to include more info about 1.0.8.
= 1.0.8 =
* Added functionality to have checkboxes for multiple mailing lists in one form.
* Added functionality to be able to change submit button text.
* Added hook after form submission.
= 1.0.7 =
* Fixed bug where form always would end up at top, when using the shortcode.
* Fixed notices that showed up.
= 1.0.6 =
* Added feature to be able to change API url
* Added https support
* Minified the js files
* Fixed notice that showed up, when in debug mode.
* Modified error handling.
= 1.0.5 =
* Fixed issue with form not submitting for logged out users.
= 1.0.4 =
* Handled issue with form not sending, when no name field.
* Fixed notices that showed up.
* Removed console message.
= 1.0.3 =
* Fixed warnings and notices that showed up, when in debug mode.
* Fixed issue with API Key, when copy/pasted key had spaces in it.
= 1.0.2 =
* Fixed error that made custom thank you message not show up, when using shortcode.
= 1.0.1 =
* Fixed error that made shortcode not showing up.
= 1.0 =
* First version
