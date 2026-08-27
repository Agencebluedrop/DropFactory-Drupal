
/**
 * Shared settings of the DropFactory platform.
 *
 * Appended to web/sites/default/default.settings.php of the platform, which
 * drush site:install copies to web/sites/<primary-domain>/settings.php for
 * every new site. Sites installed before this file existed need the same block
 * added once at the end of their own settings.php.
 *
 * The shared file sits in web/sites/, next to sites.php, so the path is built
 * from $app_root and not from $site_path.
 */
if (is_readable($app_root . '/sites/settings.shared.php')) {
  include $app_root . '/sites/settings.shared.php';
}
