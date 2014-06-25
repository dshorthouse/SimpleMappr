<?php

mb_internal_encoding("UTF-8");

mb_http_output("UTF-8");

//set the default timezone
date_default_timezone_set("America/New_York");

// define the environment as 'development', 'production', 'testing'
defined("ENVIRONMENT") || define("ENVIRONMENT", "development");

// root directory
defined("ROOT") || define("ROOT", dirname(__DIR__));

// Upload directory for API calls, without trailing slash
defined("MAPPR_UPLOAD_DIRECTORY") || define("MAPPR_UPLOAD_DIRECTORY", dirname(__DIR__)."/uploads");

// Domain from where images will be served served, without a trailing slash
defined("MAPPR_MAPS_URL") || define("MAPPR_MAPS_URL", "http://img.simplemappr.local");

// Domain for setting cookies in sessions
defined("MAPPR_DOMAIN") || define("MAPPR_DOMAIN", "www.simplemappr.local");

// Number of textarea boxes for user data entry
defined("NUMTEXTAREA") || define("NUMTEXTAREA", 3);

// Google Analytics UA-XXXXXX-XX key
defined("GOOGLE_ANALYTICS") || define("GOOGLE_ANALYTICS", "UA-XXXXXX-10");

// Private RPX_KEY for OpenID client login that can be obtained at https://rpxnow.com/
defined("RPX_KEY") || define("RPX_KEY", "");

// Cloudflare
defined("CLOUDFLARE_KEY") || define("CLOUDFLARE_KEY", "");

defined("CLOUDFLARE_DOMAIN") || define("CLOUDFLARE_DOMAIN", "");

defined("CLOUDFLARE_EMAIL") || define("CLOUDFLARE_EMAIL", "");

defined("COOKIE_TIMEOUT") || define("COOKIE_TIMEOUT", time() + (2 * 7 * 24 * 60 * 60)); //two week cookie lifetime

?>