<?php
/**
 * Dropfactory Backend - Site Class
 *
 * Represents a website on a given drupal platform
 *
 * PHP version 8
 *
 * @author  Ludovic Poujol <lpoujol@evolix.fr>
 * @author  Gregory Colpart <reg@evolix.fr>
 * @author  Evolix <info@evolix.fr>
 * @author  Elie Choufani <echoufani@bluedrop.fr>
 * @author  Bluedrop.fr <contact@bluedrop.fr>
 * @license TODO
 * @link    TODO
 */
class Site
{

    protected int    $site_id;
    protected String $site_name;
    protected String $site_platform;
    protected Int    $site_platform_id;
    protected String $site_domain;
    protected Array  $site_serveraliases = [];
    protected Int    $site_profile_id;
    protected String $site_language;
    protected String $site_status = 'DISABLED';

    protected String $site_admin_password_reset_url = "";

    protected Ansible $ansible;

    /**
     * Initiate a site
     *
     * @param String $name        Site name
     * @param Int    $platform_id the platform id (in database)
     * @param String $domain      the site domain name (fqdn)
     * @param int    $profile_id  the platform profile (identified by it's db id) for the site
     * @param String $language    the site lang
     */
    function __construct(String $name,
        Int $platform_id,
        String $domain,
        int $profile_id,
        String $language
    ) {
        $this->site_name = $name;
        $this->site_platform_id = $platform_id;
        $this->site_domain = $domain;
        $this->site_profile_id = $profile_id;
        $this->site_language = $language;
        // $this->site_serveraliases = $serveraliases;


        $platform = Platform::get_name_by_id($platform_id);
        if ($platform === false) {
            throw new InvalidArgumentException("Platform does not exist - Given platform id:". $platform_id);
        }

        $this->site_platform = $platform;
    }

    /**
     * (Placeholder) Get a site list (from database?)
     */
    static function list() : void
    {
        // ?
    }

    /**
     * TASK : Create a new site
     *
     * @param String $name        Site name
     * @param int    $platform_id The platform id
     * @param String $domain      The main domain used by the website
     * @param int    $profile_id  The profile id of the platform to use
     * @param String $language    The language to use (ex: 'fr', 'en')
     * @param Array  $aliases     The site domain aliases (server aliases)
     *
     * @return Site The newly created site
     */
    static function task_add(String $name,
        int $platform_id,
        String $domain,
        int $profile_id,
        String $language,
        array $aliases = []
    ) : Site {

        $site = new site($name, $platform_id, $domain, $profile_id, $language);
        $site->insert();
        $site->set_aliases($aliases);
        $site->create();
        $site->set_enabled();

        return $site;
    }

    /**
     * TASK : Clear site cache
     *
     * @param Int $site_id the site id
     *
     * @return Site The site we want cache:clear
     */
    static function task_clear_cache(int $site_id): Site
    {
        $site = Site::init_by_id($site_id);
        $site->clear_cache();

        return $site;
    }

    /**
     * TASK : Run site cron
     *
     * @param Int $site_id the site id
     *
     * @return Site The site we want to run cron on
     */
    static function task_run_cron(int $site_id): Site
    {
        $site = Site::init_by_id($site_id);
        $site->run_cron();

        return $site;
    }

    /**
     * TASK : Run site database updates
     *
     * @param Int $site_id the site id
     *
     * @return Site The site we want to update
     */
    static function task_db_updates(int $site_id): Site
    {
        $site = Site::init_by_id($site_id);
        $site->db_updates();

        return $site;
    }

    /**
     * TASK : Reset password
     *
     * @param Int $site_id the site id
     *
     * @return Site The site we want to fetch a login URL
     */
    static function task_reset_password(int $site_id): Site
    {
        $site = Site::init_by_id($site_id);
        $site->reset_password();

        return $site;
    }

    /**
     * TASK : Edit site
     *
     * @param Int $site_id the site id
     * @param String $name the site name
     * @param Array $aliases the site domain aliases
     *
     * @return Site The site we edited
     */
    static function task_edit(int $site_id, string $name, array $aliases): Site
    {
        $site = Site::init_by_id($site_id);
        $site->edit($name, $aliases);

        return $site;
    }

    /**
     * TASK : Disable site
     *
     * @param Int $site_id the site id
     *
     * @return Site The site we disabled
     */
    static function task_disable(int $site_id): Site
    {
        $site = Site::init_by_id($site_id);
        $site->disable();
        return $site;
    }


    /**
     * TASK : Enable site
     *
     * @param Int $site_id the site id
     *
     * @return Site The site we enabled
     */
    static function task_enable(int $site_id): Site
    {
        $site = Site::init_by_id($site_id);
        $site->enable();
        return $site;
    }

    /**
     * TASK : Delete site
     *
     * @param Int $site_id the site id
     *
     * @return Site The site we deleted
     */
    static function task_delete(int $site_id): Site
    {
        $site = Site::init_by_id($site_id);
        $site->delete();

        return $site;
    }

    /**
     * Update the site informations in database
     *
     * @return void
     */
    function update() : void
    {
        $query = 'UPDATE `Site`
                    SET
                    status = :status
                where id = :id';

        $stmt = DB::$pdo->prepare($query);

        $stmt->execute(
            ['id' => $this->site_id,
            'status' => $this->site_status]
        );
    }

    /**
     * Insert the site informations in database
     *
     * @return void
     */
    function insert() : void
    {
        $query = 'INSERT INTO `Site`
                (install_profile_id, platform_id, name, domain, language, status)
                VALUES (:install_profile_id ,:platform_id, :name, :domain, :language, :status)';

        $stmt = DB::$pdo->prepare($query);

        $stmt->execute(
            ['install_profile_id'  => $this->site_profile_id,
                        'platform_id'         => $this->site_platform_id,
                        'name'                => $this->site_name,
                        'domain'              => $this->site_domain,
                        'language'            => $this->site_language,
            'status'              => $this->site_status]
        );

        $this->site_id = DB::$pdo->lastInsertId();
    }

    /**
     * Create the site on the system (with ansible)
     *
     * @return void
     */
    function create() : void
    {
        echo "Creating the site with my little hands\n";

        $site_domains = array_merge([$this->site_domain], $this->site_serveraliases);

        $this->ansible = new Ansible("site_add.yml");
        $this->ansible->add_var("dropfactory_site_platform", $this->site_platform);
        $this->ansible->add_var("dropfactory_site_platform_id", $this->site_platform_id);
        $this->ansible->add_var("dropfactory_site_platform_user", "platform_".$this->site_platform_id);
        $this->ansible->add_var("dropfactory_site_id", $this->site_id);
        $this->ansible->add_var("dropfactory_site_domain", $site_domains);
        $this->ansible->add_var("dropfactory_site_main_domain", $this->site_domain);
        $this->ansible->add_var("dropfactory_site_aliases", $this->site_serveraliases);
        $this->ansible->add_var("dropfactory_site_profile_name", $this->get_profile_name());
        $this->ansible->add_var("dropfactory_site_db", 'platform_'.$this->site_platform_id.'_site_'.$this->site_id);
        $this->ansible->add_var("dropfactory_site_vhost", 'platform_'.$this->site_platform_id.'_site_'.$this->site_id);
        $this->ansible->run();
    }

    /**
     * Clear cache the site (with ansible)
     *
     * @return void
     */
    function clear_cache() : void
    {
        echo "Clearing cache the site\n";

        $this->ansible = new Ansible("site_clear_cache.yml");
        $this->ansible->add_var("dropfactory_site_platform", $this->site_platform);
        $this->ansible->add_var("dropfactory_site_platform_id", $this->site_platform_id);
        $this->ansible->add_var("dropfactory_site_platform_user", "platform_".$this->site_platform_id);
        $this->ansible->add_var("dropfactory_site_id", $this->site_id);
        $this->ansible->add_var("dropfactory_site_domain", array($this->site_domain));
        $this->ansible->run();
    }

    /**
     * Run cron on the site with ansible.
     *
     * @return void
     */
    function run_cron(): void
    {
        echo "Running cron on the site\n";

        $this->ansible = new Ansible("site_run_cron.yml");
        $this->ansible->add_var("dropfactory_site_platform", $this->site_platform);
        $this->ansible->add_var("dropfactory_site_platform_id", $this->site_platform_id);
        $this->ansible->add_var("dropfactory_site_platform_user", "platform_" . $this->site_platform_id);
        $this->ansible->add_var("dropfactory_site_id", $this->site_id);
        $this->ansible->add_var("dropfactory_site_domain", array($this->site_domain));
        $this->ansible->run();
    }

    /**
     * Run database updates on the site with ansible.
     *
     * @return void
     */
    function db_updates(): void
    {
        echo "Running database updates on the site\n";

        $this->ansible = new Ansible("site_db_updates.yml");
        $this->ansible->add_var("dropfactory_site_platform", $this->site_platform);
        $this->ansible->add_var("dropfactory_site_platform_id", $this->site_platform_id);
        $this->ansible->add_var("dropfactory_site_platform_user", "platform_" . $this->site_platform_id);
        $this->ansible->add_var("dropfactory_site_id", $this->site_id);
        $this->ansible->add_var("dropfactory_site_domain", array($this->site_domain));
        $this->ansible->run();
    }

    /**
     * Reset admin password of the site (with ansible)
     *
     * @return void
     */
    function reset_password() : void
    {
        echo "Reseting password\n";

        $this->ansible = new Ansible("site_reset_password.yml");
        $this->ansible->add_var("dropfactory_site_platform", $this->site_platform);
        $this->ansible->add_var("dropfactory_site_platform_id", $this->site_platform_id);
        $this->ansible->add_var("dropfactory_site_platform_user", "platform_".$this->site_platform_id);
        $this->ansible->add_var("dropfactory_site_id", $this->site_id);
        $this->ansible->add_var("dropfactory_site_domain", array($this->site_domain));

        $this->ansible->run();

        // fetch the reset password URL from the ansible logs output
        $logs = $this->ansible->get_logs();
        $this->site_admin_password_reset_url = $logs->plays[0]->tasks[1]->hosts->localhost->stdout;
    }

    /**
     * Edit a site : update its name and aliases (database), then
     * reconfigure the system (vhost + Drupal multisite) with Ansible.
     *
     * The Ansible reconfiguration only runs if the database changes were
     * committed successfully, to avoid a divergence between the database
     * and the system.
     *
     * @param String $name    The new site name
     * @param Array  $aliases The site domain aliases
     *
     * @return void
     */
    function edit(string $name, array $aliases): void
    {
        $aliases = $this->normalize_aliases($aliases);

        DB::$pdo->beginTransaction();

        try {
            $stmt = DB::$pdo->prepare('
                UPDATE `Site`
                SET name = :name
                WHERE id = :id
            ');
            $stmt->execute([
                'id' => $this->site_id,
                'name' => $name,
            ]);
            $this->site_name = $name;

            $this->delete_aliases();
            $this->insert_aliases($aliases);

            DB::$pdo->commit();
        } catch (\Throwable $e) {
            DB::$pdo->rollBack();
            throw new RuntimeException(
                'Failed to edit site in database: ' . $e->getMessage(),
                0,
                $e
            );
        }

        echo "Updating site configuration with Ansible\n";

        $this->site_serveraliases = $aliases;
        $site_domains = array_merge([$this->site_domain], $this->site_serveraliases);

        $this->ansible = new Ansible("site_edit.yml");
        $this->ansible->add_var("dropfactory_site_platform", $this->site_platform);
        $this->ansible->add_var("dropfactory_site_platform_id", $this->site_platform_id);
        $this->ansible->add_var("dropfactory_site_platform_user", "platform_" . $this->site_platform_id);
        $this->ansible->add_var("dropfactory_site_id", $this->site_id);

        $this->ansible->add_var("dropfactory_site_domain", $site_domains);
        $this->ansible->add_var("dropfactory_site_main_domain", $this->site_domain);
        $this->ansible->add_var("dropfactory_site_aliases", $this->site_serveraliases);

        $this->ansible->add_var(
            "dropfactory_site_vhost",
            "platform_" . $this->site_platform_id . "_site_" . $this->site_id);

        $this->ansible->run();
    }

    /**
     * Normalize a list of aliases : trim, drop empty values and the main
     * domain, and remove duplicates.
     *
     * @param Array $aliases The raw aliases
     *
     * @return Array The normalized aliases
     */
    private function normalize_aliases(array $aliases): array
    {
        return array_values(
            array_unique(
                array_filter(
                    array_map(static fn ($alias) => trim((string) $alias), $aliases),
                    fn ($alias) => $alias !== '' && $alias !== $this->site_domain
                )
            )
        );
    }

    /**
     * Persist the given aliases for the current site in the database.
     *
     * @param Array $aliases The (normalized) aliases to insert
     *
     * @return void
     */
    private function insert_aliases(array $aliases): void
    {
        $stmt = DB::$pdo->prepare('
            INSERT INTO `Alias` (site_id, domain)
            VALUES (:site_id, :domain)
        ');

        foreach ($aliases as $alias) {
            $stmt->execute([
                'site_id' => $this->site_id,
                'domain' => $alias,
            ]);
        }
    }

    /**
     * Remove every alias tied to the current site from the database.
     *
     * @return void
     */
    private function delete_aliases(): void
    {
        $stmt = DB::$pdo->prepare('DELETE FROM `Alias` WHERE site_id = :site_id');
        $stmt->execute(['site_id' => $this->site_id]);
    }

    /**
     * Normalize and persist the site aliases (used at creation time).
     *
     * @param Array $aliases The raw aliases
     *
     * @return void
     */
    function set_aliases(array $aliases): void
    {
        $this->site_serveraliases = $this->normalize_aliases($aliases);
        $this->insert_aliases($this->site_serveraliases);
    }

    /**
     * Disable a site (with ansible)
     *
     * @return void
     */
    function disable() : void
    {
        echo "Disable site\n";

        $this->ansible = new Ansible("site_disable.yml");
        $this->ansible->add_var("dropfactory_site_platform", $this->site_platform);
        $this->ansible->add_var("dropfactory_site_platform_id", $this->site_platform_id);
        $this->ansible->add_var("dropfactory_site_platform_user", "platform_".$this->site_platform_id);
        $this->ansible->add_var("dropfactory_site_id", $this->site_id);
        $this->ansible->add_var("dropfactory_site_domain", array($this->site_domain));
        $this->ansible->add_var("dropfactory_site_vhost", 'platform_'.$this->site_platform_id.'_site_'.$this->site_id);


        $this->ansible->run();

        // Update status
        $this->site_status = 'DISABLED';
        $this->update();
    }

    /**
     * Enable a site (with ansible)
     *
     * @return void
     */
    function enable() : void
    {
        echo "Enable site\n";

        $this->ansible = new Ansible("site_enable.yml");
        $this->ansible->add_var("dropfactory_site_platform", $this->site_platform);
        $this->ansible->add_var("dropfactory_site_platform_id", $this->site_platform_id);
        $this->ansible->add_var("dropfactory_site_platform_user", "platform_".$this->site_platform_id);
        $this->ansible->add_var("dropfactory_site_id", $this->site_id);
        $this->ansible->add_var("dropfactory_site_domain", array($this->site_domain));
        $this->ansible->add_var("dropfactory_site_vhost", 'platform_'.$this->site_platform_id.'_site_'.$this->site_id);


        $this->ansible->run();

        // Update status
        $this->site_status = 'ENABLED';
        $this->update();
    }

    /**
     * Delete a site with ansible.
     *
     * This creates a backup, removes the vhost, deletes the Drupal multisite
     * folder, drops the MySQL user and drops the database.
     *
     * @return void
     */
    function delete(): void
    {
        echo "Delete site\n";

        $this->ansible = new Ansible("site_delete.yml");
        $this->ansible->add_var("dropfactory_site_platform", $this->site_platform);
        $this->ansible->add_var("dropfactory_site_platform_id", $this->site_platform_id);
        $this->ansible->add_var("dropfactory_site_platform_user", "platform_" . $this->site_platform_id);
        $this->ansible->add_var("dropfactory_site_id", $this->site_id);
        $this->ansible->add_var("dropfactory_site_domain", array($this->site_domain));
        $this->ansible->add_var("dropfactory_site_db", "platform_" . $this->site_platform_id . "_site_" . $this->site_id);
        $this->ansible->add_var("dropfactory_site_vhost", "platform_" . $this->site_platform_id . "_site_" . $this->site_id);
        $this->ansible->run();

        if ($this->ansible->is_okay()) {
            $this->site_status = "DELETED";
            $this->update();
        }
    }

    /**
     * Initialize a Site Object with a database id
     *
     * @param Int $site_id The site id in database
     *
     * @return Site A site object initialized with informations from database
     */
    static function init_by_id(int $site_id) : Site
    {
        $query = 'SELECT `id`, `platform_id`, `name`, `domain`, `language`, `install_profile_id`, `status` FROM `Site`
                  where `id` = :id';
        $stmt = DB::$pdo->prepare($query);

        $stmt->execute(['id' => $site_id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $site = new Site($result['name'], $result['platform_id'], $result['domain'], $result['install_profile_id'], $result['language']);
        $site->set_id($site_id);
        return $site;
    }


    /**
     * Set site as enabled & update the status in database
     *
     * @return void
     */
    function set_enabled() : void
    {
        $this->site_status = 'ENABLED';
        $this->update();
    }


    /**
     * Get logs from Ansible
     *
     * @return String Json encoded log output of Ansible
     */
    function get_logs() : String
    {
        return json_encode($this->ansible->get_logs());
    }

    /**
     * Check if ansible return code was 0 (ok) or not
     *
     * @return Bool : True if ansible exec returned 0, false otherwise
     */
    function get_ansible_status() : bool
    {
        return $this->ansible->is_okay();
    }

    /**
     * Return the site's profile name
     *
     * @return String the profile's name
     */
    function get_profile_name(): String
    {
        $query = 'SELECT `name` FROM `Profile`
                  WHERE `id` = :install_profile_id AND `platform_id` = :platform_id';
        $stmt = DB::$pdo->prepare($query);
        $stmt->execute([
            'install_profile_id' => $this->site_profile_id,
            'platform_id'        => $this->site_platform_id,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            throw new InvalidArgumentException(
                'Profile not found for install_profile_id '
                . $this->site_profile_id . ' and platform_id ' . $this->site_platform_id
            );
        }

        return $row['name'];
    }

    /**
     * Return the site ID
     *
     * @return int the site id
     */
    function get_id(): int
    {
        return $this->site_id;
    }

    /**
     * Set the id
     *
     * @param Int $site_id The site id (in database)
     *
     * @return void
     */
    function set_id(int $site_id): void
    {
        $this->site_id = $site_id;
    }

    /**
     * Return the password reset/login URL for the website
     * (after a reset_password task)
     *
     * @return String The password reset URL
     */
    function get_password_reset_url() : String
    {
        return $this->site_admin_password_reset_url;
    }
}
