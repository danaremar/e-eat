=== Menu Image, Icons made easy ===
Contributors: takanakui, freemius
Tags: menu, navigation, image, icons, nav menu
Donate link: https://www.buymeacoffee.com/ruiguerreiro
Requires at least: 4.4.0
Tested up to: 5.6
Stable tag: 3.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds an image or icon in the menu items. You can choose the position of the image (after, before, above, below) or even hide the menu item title.


== Description ==

Easily add an image or icon in a menu item. Creating a better website menu.
Control the position of the image or icon and also it's size.

With Menu Image plugin you can do more, check some of the features:

- [New] FontAwesome Icons
- [New] DashIcons Icons
- Hide Title and show only image or icon.
- Add Image / Icon on the Left of the menu item title.
- Add Image / Icon on the Right of the menu item title.
- Add Image / Icon on the Above of the menu item title.
- Add Image / Icon on the Below of the menu item title.
- Switch images / icons on mouse over the menu item.
- [PREMIUM] Convert menu items into Call to action buttons.
- [PREMIUM] Add count bubble to menu items (Cart total, category total, custom function) menu items.
- [PREMIUM] Notification badges on the menu items (New, Sale, Hiring, etc).
- [PREMIUM] Color customization of the buttons, badges and bubbles.
- [PREMIUM] Disable Menu Image in Mobile devices


= Links =

* [Menu Image Premium](https://www.wpmenuimage.com/?utm_source=wprepo-readme&utm_medium=user%20website&utm_campaign=readme_link)
* [Documentation](https://www.wpmenuimage.com/knowledgebase/?utm_source=wprepo-readme&utm_medium=user%20website&utm_campaign=readme_link)
* [Creat support ticket](https://www.wpmenuimage.com/support-contact/?utm_source=wprepo-readme&utm_medium=user%20website&utm_campaign=readme_link)

= Related Plugins =
* [Mobile Menu](https://www.wpmobilemenu.com/?utm_source=wordpressorg&utm_medium=menu-image&utm_campaign=plugin-description): WP Mobile Menu is the best WordPress responsive mobile menu. Provide to your mobile visitor an easy access to your site content using any device smartphone/tablet/desktop.

What people is saying!

> `Easy to use and good author support`
> This plugin is a good solution to easily include an image in a menu. Excellent support from plugin author!

> @dwoolworth824

> `Best plugin for adding a logo to your navigation`
> I'd definitely recommend this plugin if you need to add a logo to your navigation in WordPress. The support provided is so 5 stars!

> @manmade1
== Installation ==

1. Upload `menu-image` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to `/wp-admin/nav-menus.php`
4. Edit exist menu item or add new menu item and just upload image than click `Save Menu`
5. See your menu on site
6. (WMPL users only) Goto WPML > WP Menus Sync and click to `Sync`

== Frequently Asked Questions ==

= How to install Menu Image? =

Check this article in the following [link](https://www.wpmenuimage.com/knowledgebase/getting-started/how-to-install-menu-image/?utm_source=wprepo-readme&utm_medium=user%20website&utm_campaign=readme_faqs_2)

= How to enable Menu Image? =

Check this article in the following [link](https://www.wpmenuimage.com/knowledgebase/general-options/how-to-enable-menu-image/?utm_source=wprepo-readme&utm_medium=user%20website&utm_campaign=readme_faqs_2)

= How to add Icons to Menu Items? =

Check this article in the following [link](https://www.wpmenuimage.com/knowledgebase/general-options/add-icons-to-menu-items/?utm_source=wprepo-readme&utm_medium=user%20website&utm_campaign=readme_faqs_2)

= How to add custom CSS to Menu Image? =

Check this article in the following [link](https://www.wpmenuimage.com/knowledgebase/general-options/how-to-add-custom-css-to-menu-image/?utm_source=wprepo-readme&utm_medium=user%20website&utm_campaign=readme_faqs_2)


== Screenshots ==

1. Add FontAwesome Icons to menu item
2. Add DashIcons Icons to menu item
3. Add images to menu item

== Changelog ==

### 3.0.2 ###
* Fix - Load Dashicons when the user isn't logged in
* Fix - General improvments

### 3.0.1 ###
* New - Add RTL CSS in Admin settings
* Fix - Image size settings wasn't saving
* Fix - Avoid HTML Markup in items without Icons or images
* Fix - Remove FontAwesome Enqueue

### 3.0 ###
* New - New Settings redesign
* New - Menu item preview in the settings
* New - Possible to add FontAwesome Icons
* New - Possible to add DashIcons Icons
* Improvment – Update Freemius SDK
* Improvment – Code clean up

### 2.9.7 ###
* Fix - Fix Issue with display title above and below
* Improvment – Update Freemius SDK to 2.4.0.1

### 2.9.6 ###
* Fix - Fix compatibility issue with WordPress 5.4.

### 2.9.5 ###
* Fix - Remove unnecessary filter.
* Fix - Adjust the CSS for title below.

### 2.9.4 ###
* Fix - Bug of the duplicated images.

### 2.9.3 ###
* New - Add compatibility with Max Megamenu.
* New - Add new filter to change the markup of the image
* Fix - Lower the Menu Image options to be below the WordPress Settings.
* Fix - Update Mobile Menu Link.
* Fix - Relocate CSS and JS resources.

### 2.9.2 ###
* New - Include Freemius framework.
* New - Settings panel.
* New - Option to enable/disable image on hover.
* New - Options to change the custom image sizes.


### 2.9.1 ###
* Fix previous broken update. Sorry for that, everyone is mistake.
* Remove images srcset and sizes attributes.
* Add autotests on for images view.

### 2.9.0 ###
* Update admin part copy regarding to new wp version.
* Fix support url.
* Fix php warning.

= 2.8.0 =
* Use core `nav_menu_link_attributes`, `nav_menu_item_title` filters to add image and class instead of `walker_nav_menu_start_el` filter.
* Drop support of core version < 4.4.0.

