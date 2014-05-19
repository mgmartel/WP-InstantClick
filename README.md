WP InstantClick
=========

WP InstantClick is a simple plugin that adds [InstantClick](https://github.com/dieulot/instantclick/) to your WordPress website instantly. Also on [WP.org](http://wordpress.org/plugins/instantclick/).

What is it?
---
InstantClick is a JavaScript library that dramatically speeds up your website, making navigation effectively instant in most cases. This plugin is the easiest way of adding InstantClick to your WordPress theme.

See [InstantClick.io](http://instantclick.io/) for more information on InstantClick.

You can either use the plugin as-is, or include the files in your theme.

Usage as plugin
---
When you add the plugin to your WordPress install, it will automatically load InstantClick on your website. In WP-Admin you will find a new options page called 'InstantClick' under 'Settings'. On the options page you can:

* Set the preload mode (on hover with a 0, 50 or 100ms delay, or on mousedown)
* Exclude scripts from reloading with InstantClick (adding `data-no-instant`)
* Add custom JavaScript to be loaded before or after InstantClick has initialized

Include it in a theme
---

Add all files from this repo to your theme. In your `functions.php` (or whatever place you use for your theme scripts) and include `class-wp-instantclick.php`. After this, call `WP_InstantClick::enable();`. To also enable the options page, include `class-wp-instantclick-options.php` and call `WP_InstantClick_Options::enable();`.

By default, InstantClick will start preloading as soon as the visitor hovers over a link. This may not be the ideal setup with your theme. You can programmatically set InstantClick to preload only after a delay or on mousedown. Use the following methods to set that up:

* **On mousedown:** `WP_InstantClick::preload_on_mousedown();`
* **On hover after a delay**: `WP_InstantClick::preload_on_hover( $delay_in_milliseconds );`

Fine Tuning
---
See the documentation on [InstantClick.io](http://instantclick.io/download) for details on how the script works and how to ensure compatibility with other scripts. WordPress does not allow you to add `data-` attributes to enqueued scripts out of the box, so the `WP_InstantClick` class includes a method for adding the `data-no-instant` attribute.

When using the plugin, you can add the script handle on the settings page to add the `data-no-instant` attribute.

To do it programmatically, simply call `WP_InstantClick::no_instant( $handle )` with the handle of the script. For example:
```php
add_action( 'wp_enqueue_scripts', 'my_theme_script_enqueue' );
function my_theme_script_enqueue() {
    wp_enqueue_script( 'my-script-handle' );
    WP_InstantClick::no_instant( 'my-script-handle' );
}
```

To add any extra scripts, use the hooks `instantclick_before_init` and `instantclick_after_init`, called inside the `<script>` tag before and after `InstantClick.init();` respectively. For example:

```php
add_action( 'instantclick_before_init', function() {
    ?>
    InstantClick.on('change', function() {
        ga('send', 'pageview', location.pathname + location.search);
    });
    <?php
});
```


License
---
MIT