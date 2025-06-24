<?php
/**
 * Dropfactory Backend - Platform Class
 * 
 * Represents a drupal platform
 * 
 * PHP version 8
 * 
 * @author  Ludovic Poujol <lpoujol@evolix.fr>
 * @author  Gregory Colpart <reg@evolix.fr>
 * @author  Evolix <info@evolix.fr>
 * @license TODO
 * @link    TODO
 */
class Platform
{

    protected int    $platform_id;
    protected String $platform_name;
    protected String $platform_git_url;
    protected String $platform_git_branch;
    protected String $platform_status = "DISABLED";

    protected Ansible $ansible;

    function __construct(String $name, String $git_url, String $git_branch)
    {
        $this->platform_name = $name;
        $this->platform_git_url = $git_url;
        $this->platform_git_branch = $git_branch;
    }

    /** 
     * (Placeholder) Get a platform list (from database?)
     */
    static function list()
    {
        
    }

    /** 
     * Find a platform by name and return it's internal database id
     * 
     * @param  String platform_name The platform name
     * @return int|false The platform id or false if no platform was found
     */
    static function get_id_by_name(String $platform_name) : int | false
    {
        $query = 'SELECT `id`, `name` FROM `Platform` 
                  where `name` = :name';

        $stmt = DB::$pdo->prepare($query);

        $stmt->execute(['name' => $platform_name]);

        if($stmt->rowcount() !== 1) {
            return false;
        }

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['id'];

    }

    /** 
     * Find a platform by id and return it's name
     * 
     * @param  int platform_id The platform id
     * @return String|false The platform name or false if no platform was found
     */
    static function get_name_by_id(int $platform_id) : String | false
    {
        $query = 'SELECT `id`, `name` FROM `Platform` 
                  where `id` = :id';

        $stmt = DB::$pdo->prepare($query);

        $stmt->execute(['id' => $platform_id]);

        if($stmt->rowcount() !== 1) {
            return false;
        }

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['name'];

    }

    /**
     * Initialize a Platform Object with a database id
     * 
     * @param Int platform_id The platform id in database
     */
    static function init_by_id(int $platform_id) : Platform
    {
        $query = 'SELECT `id`, `name`, `gitRepositoryURL`, `gitRepositoryBranch` FROM `Platform` 
                  where `id` = :id';
        $stmt = DB::$pdo->prepare($query);

        $stmt->execute(['id' => $platform_id]);


        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $platform = new Platform($result['name'], $result['gitRepositoryURL'], $result['gitRepositoryBranch']);
        $platform->set_id($platform_id);

        return $platform;
    }

    /**
     * Add a new platform (as a task)
     * 
     * @param  String name Platform name (used as UNIX account)
     * @param  String git_url Git URL to pull the platform code
     * @param  String git_branch The git branch to use
     * @return Platform The newly created platform
     */
    static function task_add(String $name, String $git_url, String $git_branch) : Platform
    {

        // TODO : Dirty placeholder. 
        // Here a real check that a given platform does not exist before adding it
        $platform_id = Platform::get_id_by_name($name);

        if($platform_id !== false) {
            throw new InvalidArgumentException("Platform already exists. Platformname was : ".$name);
        }
        // END placeholder

        $platform = new Platform($name, $git_url, $git_branch);
        $platform->insert();
        $platform->create();
        $platform->set_enabled();
        
        return $platform;
    }

    /**
     * Add a new platform (as a task)
     * 
     * @param Int name Platform id
     */
    static function task_pull(int $platform_id) : Platform
    {       
        $platform = Platform::init_by_id($platform_id);

        $platform->pull();
        
        return $platform;
    }

    /**
     * Update the platform informations in database
     */
    function update() : void
    {
        $query = 'UPDATE `Platform` 
                  SET
                    status = :status
                where id = :id';
        
        $stmt = DB::$pdo->prepare($query);

        $stmt->execute(
            ['id' => $this->platform_id, 
            'status' => $this->platform_status]
        );
    }

    /**
     * Insert the platform informations in database
     */
    function insert() : void
    {
        $query = 'INSERT INTO `Platform` 
                (name, status, gitRepositoryURL, gitRepositoryBranch) 
                VALUES (:name, :status, :gitRepositoryURL, :gitRepositoryBranch)';
        
        $stmt = DB::$pdo->prepare($query);

        $stmt->execute(
            ['name' => $this->platform_name, 
                        'status' => $this->platform_status,
                        'gitRepositoryURL' => $this->platform_git_url,
            'gitRepositoryBranch' => $this->platform_git_branch]
        );

        $this->platform_id = DB::$pdo->lastInsertId();


        // Placeholder to meet db constrains
        $query = 'INSERT INTO `Profile` 
        (platform_id, name) 
        VALUES (:platform_id, :name)';

        $stmt = DB::$pdo->prepare($query);

        $stmt->execute(
            ['platform_id' => $this->platform_id, 
            'name' => 'default']
        );
    }

    /**
     * Create the platform on the system (with ansible)
     */
    function create() : void
    {
        echo "Creating the platform with my little hands\n";

        $this->ansible = new Ansible("platform_add.yml");

        $this->ansible->add_var("dropfactory_platform_unix_user", $this->get_unix_user());
        $this->ansible->add_var("dropfactory_platform_git_url", $this->platform_git_url);
        $this->ansible->add_var("dropfactory_platform_git_branch", $this->platform_git_branch);
        
        $this->ansible->run();
    }


    /**
     * (Git) Pull the platform code (with ansible)
     */
    function pull() : void
    {
        echo "Pulling the platform with my little hands\n";

        $this->ansible = new Ansible("platform_pull.yml");

        $this->ansible->add_var("dropfactory_platform_unix_user", $this->get_unix_user());
        $this->ansible->add_var("dropfactory_platform_git_url", $this->platform_git_url);
        $this->ansible->add_var("dropfactory_platform_git_branch", $this->platform_git_branch);
        
        $this->ansible->run();
    }



    /**
     * Set platform as enabled
     */
    function set_enabled() : void
    {
        $this->platform_status = 'ENABLED';
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
     * Return the platform ID
     * 
     * @return int the platform id
     */
    function get_id(): int
    {
        return $this->platform_id;
    }

    /**
     * 
     */
    function set_id(int $platform_id): void
    {
        $this->platform_id = $platform_id;
    }


    /**
     * Return the UNIX account name used for the platform
     */
    function get_unix_user() : String 
    {
        return "platform_".$this->platform_id;
    }


}
