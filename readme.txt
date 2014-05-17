=== Plugin Name ===
Contributors: Mike_Cowobo
Tags: performance, instantclick, links
Requires at least: 3.9
Tested up to: 3.9.1
Stable tag: 0.9
License: MIT
License URI: http://opensource.org/licenses/MIT

Dramatically speed up your website and make navigation effectively instant by loading the next link on hover.

== Description ==

InstantClick is a JavaScript library that dramatically speeds up your website, making navigation effectively instant in most cases. This plugin is the easiest way of adding InstantClick to your WordPress theme.

See [InstantClick.io](http://instantclick.io/) for more information on InstantClick. In summary:

> Before visitors click on a link, they hover over that link. Between these two events, 200 ms to 300 ms usually pass by ([test yourself here](http://instantclick.io/click-test)). InstantClick makes use of that time to preload the page, so that the page is already there when you click.

This plugin simply adds InstantClick to your website using the WordPress scripts API. For further tweaking, see the Installation notes.

== Installation ==

Install and activate the plugin. InstantClick should be automatically added to your website.

By default, InstantClick will start preloading as soon as the visitor hovers over a link. This may not be the ideal setup for your website. You can tell InstantClick to preload only after a delay or on mousedown. Add the following lines to your theme's `functions.php` or your functions plugin to change the default behaviour:

* **On mousedown:** `WP_InstantClick::preload_on_mousedown();`
* **On hover after a delay**: `WP_InstantClick::preload_on_hover( $delay_in_ms );`

= Fine Tuning =

See the documentation on [InstantClick.io](http://instantclick.io/download) for details on how the script works and how to ensure compatibility with other scripts. WordPress does not allow you to add `data-` attributes to enqueued scripts out of the box, so the `WP_InstantClick` class includes a method for adding the `data-no-instant` attribute. After enqueueing a script, simply call `WP_InstantClick::no_instant( $handle )` with the handle of the script. For example:

`
add_action( 'wp_enqueue_scripts', 'my_theme_script_enqueue' );
function my_theme_script_enqueue() {
    wp_enqueue_script( 'my-script-handle' );
    WP_InstantClick::no_instant( 'my-script-handle' );
}
`

To add any extra scripts, use the hooks `instantclick_before_init` and `instantclick_after_init`, called inside the `<script>` tag before and after `InstantClick.init();` respectively. For example:

`
add_action( 'instantclick_before_init', function() {
    ?>
    InstantClick.on('change', function() {
        ga('send', 'pageview', location.pathname + location.search);
    });
    <?php
});
`

== Frequently Asked Questions ==

None yet. Checkout the support forum, the [GitHub repo](https://github.com/mgmartel/WP-InstantClick) and [InstantClick](http://instantclick.io) website for the answers to most questions.

== Screenshots ==


== Changelog ==

= 1.0 =
* Initial release to WP.org plugin repository.
