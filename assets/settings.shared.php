<?php

/**
 * Template of the shared Drupal settings of a DropFactory platform.
 *
 * DropFactory does not write this file : it belongs to the platform git
 * repository, in web/sites/settings.shared.php, and it is included by the
 * settings.php of every site of the platform. See assets/default.settings.append.php
 * for the include, and the README for the Composer scaffold mapping that
 * protects it.
 *
 * Drupal provides $app_root and $site_path when settings.php is loaded :
 *   $app_root  = /home/platform_<ID>/platform/web
 *   $site_path = sites/<primary-domain>
 */

/**
 * Trusted host patterns, derived from the aliases of the site.
 *
 */

/**
 * $site_path is a variable defined in $SETTINGS 
 * and contains the name of the current site's directory.
 * The site installation is launched with --sites-subdir='XXX' 
 * (where XXX is the site's primary domain), 
 * so the directory name is always the primary domain.
 */
$dropfactory_dir = basename($site_path);

// Defined here too, in case sites.php is missing or unreadable.
$sites = [];
if (is_readable($app_root . '/sites/sites.php')) {
  include $app_root . '/sites/sites.php';
}

// sites.php holds entries like $sites['www.example.fr'] = 'example.fr'; and is
// shared by every site of the platform, hence the filtering on the current one.

/**
 * If sites.php is available (it contains entries such as 
 * $sites['www.mon-site.fr'] = 'mon-site.example.fr';),
 * the $sites variable will be available here in our core.
 * We look for all aliases in the $sites variable that correspond to the current domain
 *  (filtering is necessary because sites.php is shared across all platforms).
 */
$dropfactory_hosts = array_unique(array_merge(
  [$dropfactory_dir],
  array_keys($sites, $dropfactory_dir, TRUE)
));

//Build the trusted host patterns array, with the current domain and all aliases.
$settings['trusted_host_patterns'] = array_map(
  static fn (string $host): string => '^' . preg_quote($host) . '$',
  $dropfactory_hosts
);

/**
 * Private files.
 *
 * DropFactory creates /home/platform_<ID>/private/<primary-domain>/ at site
 * creation, outside of the web root and of the platform git clone.
 * dirname($app_root, 2) is the platform home directory.
 */
$settings['file_private_path'] = dirname($app_root, 2) . '/private/' . basename($site_path);