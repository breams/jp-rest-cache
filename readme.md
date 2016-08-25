WP REST API Cache (based on [JP REST API CACHE](https://github.com/Shelob9/jp-rest-cache))
=====================

Soft-expiring, server-side cache for the WordPress REST API (WP REST).

Utilises the [WP-TLC-Transients](https://github.com/markjaquith/WP-TLC-Transients) library. Requires WordPress and the [WordPress REST API v2](http://v2.wp-api.org).

Essentially implements JP REST API CACHE and WP-TLC-Transients as a WordPress plugin not requiring any other PHP dependencies (namely composer).

The plugin attaches to the `rest_pre_dispatch` filter in the WP REST API and includes two filters of its own:
- `wp_rest_cache_skip_cache` can be used to filter whether a given endpoint/method should skip the cache or not (defaults to false).
- `wp_rest_cache_cache_time` can be used to change the caching timeout for the plugin (defaults to 360 seconds).

Most of the work in this was done by either [Josh Pollock](https://github.com/Shelob9) or [Mark Jaquith](http://markjaquith.com), I just put it together and tweaked it a bit.

### License
Copyright for portions of WP REST API Cache are held by Josh Pollock, 2014 as part of JP REST API CACHE, and Mark Jaquith, 2013 as part of WP TLC Transients. All other copyright for WP REST API Cache are held by Jeremy Tweddle, 2016.
WP REST API Cache is licensed under the GPL, version 2.0 or any later version. See license.txt