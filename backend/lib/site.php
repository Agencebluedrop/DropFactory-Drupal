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
    protected Array  $site_serveraliases;
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
     * 
     * @return Site The newly created site
     */
    static function task_add(String $name, 
        int $platform_id,
        String $domain,
        int $profile_id,
        String $language
    ) : Site {

        $site = new site($name, $platform_id, $domain, $profile_id, $language);
        $site->insert();
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

        $this->ansible = new Ansible("site_add.yml");
        $this->ansible->add_var("dropfactory_site_platform", $this->site_platform);
        $this->ansible->add_var("dropfactory_site_platform_id", $this->site_platform_id);
        $this->ansible->add_var("dropfactory_site_platform_user", "platform_".$this->site_platform_id);
        $this->ansible->add_var("dropfactory_site_id", $this->site_id);
        $this->ansible->add_var("dropfactory_site_domain", array($this->site_domain));
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
