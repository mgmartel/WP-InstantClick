WP InstantClick
=========

WP InstantClick is a simple plugin that adds InstantClick to your WordPress website instantly.

What is it?
---
InstantClick is a JavaScript library that dramatically speeds up your website, making navigation effectively instant in most cases. This plugin is the easiest way of adding InstantClick to your WordPress theme.

See [InstantClick.io](http://instantclick.io/) for more information on InstantClick.

Usage
---
You can either use the plugin as-is, or include the files in your theme.

* **As a plugin:** Add the plugin to your WordPress website and activate it. InstantClick will do the rest. See instructions below on how to add the `data-no-instant` attribute to specific scripts.
* **Part of your theme:** Add all files from this repo to your theme. In your `functions.php` (or whatever place you use for your theme scripts) add include `class-wp-instantclick.php`. After this, call `WP_InstantClick::enable();`.

Fine Tuning
---
See the documentation on [InstantClick.io](http://instantclick.io/download) for details on how the script works and how to ensure compatibility with other scripts. WordPress does not allow you to add `data-` attributes to enqueued scripts out of the box, so the `WP_InstantClick` class includes a method for adding the `data-no-instant` attribute. After enqueueing a script, simply call `WP_InstantClick::no_instant( $handle )` with the handle of the script. For example:
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