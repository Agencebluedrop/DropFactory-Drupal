<?php
/**
 * Dropfactory Backend - Task Class
 * 
 * Represents a task to be handled
 * 
 * PHP version 8
 * 
 * @author  Ludovic Poujol <lpoujol@evolix.fr>
 * @author  Gregory Colpart <reg@evolix.fr>
 * @author  Evolix <info@evolix.fr>
 * @license TODO
 * @link    TODO
 */
class Task
{

    protected int      $tasks_buffer_id;
    protected DateTime $tasks_buffer_created_at;
    protected string   $tasks_buffer_action;
    protected stdClass $tasks_buffer_parameters;

    protected int           $task_id;
    protected string        $task_status;
    protected int           $task_source_entity;
    protected DateTime|null $task_started_at     = null;
    protected DateTime|null $task_ended_at       = null;


    function __construct(
        int $buffer_id,
        $buffer_created_at,
        $buffer_action,
        $buffer_parameters
    ) {
        $this->tasks_buffer_id = $buffer_id;
        $this->tasks_buffer_created_at = new DateTime($buffer_created_at);
        $this->tasks_buffer_action = $buffer_action;
        $this->tasks_buffer_parameters = json_decode($buffer_parameters);

        $this->task_status = "PENDING";
    }


    /** 
     * Validate task parameters (placeholder)
     * This fuction does nothing
     */
    function validate() : void
    {
        echo "ok \n";
    }

    /**
     * Insert the task into the Task table 
     */
    function insert() : void
    {

        $query = 'INSERT INTO `Task` 
                (created_at, status, action, parameters) 
                VALUES (:created_at, :status, :action, :parameters)';
        
        $stmt = DB::$pdo->prepare($query);

        $stmt->execute(
            ['created_at' => $this->tasks_buffer_created_at->format("Y-m-d H:i:s"), 
                        'status' => $this->task_status,
                        'action' => $this->tasks_buffer_action,
            'parameters' => json_encode($this->tasks_buffer_parameters)]
        );

        $this->task_id = DB::$pdo->lastInsertId();
    }

    /**
     * Update the task information in database
     * This function will update the task in database according to the object attributes
     * This only covers the start/end timestamps, the status and action type
     */
    function update() : void
    {

        $query = 'UPDATE `Task` 
        SET
            started_at = :started_at,
            ended_at = :ended_at,
            status = :status,
            action = :action
        WHERE id = :task_id';

        $stmt = DB::$pdo->prepare($query);

        $stmt->execute(
            ['started_at' => ($this->task_started_at !== null ) ? $this->task_started_at->format("Y-m-d H:i:s") : null , 
                        'ended_at' => ($this->task_ended_at !== null ) ? $this->task_ended_at->format("Y-m-d H:i:s") : null ,
                        'status' => $this->task_status,
                        'action' => $this->tasks_buffer_action,
            'task_id' => $this->task_id ]
        );

    }

    /**
     * Set the source entity of the task
     * It's the database id of the entity tied to the action
     * (ie: if it's a PLATFORM_ADD action of the newly created platform in database )
     * 
     * @param int $source_entity Database ID of the entity related to the task
     */
    function set_source_entity(int $source_entity) : void
    {

        $this->task_source_entity = $source_entity;

        $query = 'UPDATE `Task` 
        SET source_entity = :source_entity
        WHERE id = :task_id';

        $stmt = DB::$pdo->prepare($query);

        $stmt->execute(
            ['source_entity' => $this->task_source_entity,
            'task_id' => $this->task_id ]
        );
    }

    /**
     * Remove the task from the TaskBuffer table
     */
    function remove_from_buffer() : void
    {
        $query = 'DELETE FROM `TaskBuffer` 
        where id = :id';

        $stmt = DB::$pdo->prepare($query);

        $stmt->execute(['id' => $this->tasks_buffer_id ]);
    }

    /**
     * Change task state to RUNNING
     * Also, sets the "Started At" timestamp at the current time
     */
    function begin() : void
    {
        $this->task_status = "RUNNING";
        $this->task_started_at = new DateTime();
    }

    /**
     * Change task state to SUCCESS
     * Also, sets the "Ended At" timestamp at the current time
     */
    function end() : void
    {
        $this->task_status = "SUCCESS";
        $this->task_ended_at = new DateTime();
    }

    /**
     * Change task state to WARNING
     * Also, sets the "Ended At" timestamp at the current time
     */
    function end_warning() : void
    {
        $this->task_status = "WARNING";
        $this->task_ended_at = new DateTime();
    }

    /**
     * Change task state to FAILED
     * Also, sets the "Ended At" timestamp at the current time
     */
    function set_failed() : void
    {
        $this->task_status = "FAILED";
        $this->task_ended_at = new DateTime();
    }

    /**
     * Handle the task operations
     */
    function do() : void
    {

        // Change task status to RUNNING
        $this->begin();
        $this->update();

        try {

            // Handle the different type of tasks
            switch ($this->tasks_buffer_action) {
                // -- Platform related tasks
            case 'PLATFORM_ADD':
                echo "Doing PLATFORM_ADD\n";

                $platform = Platform::task_add($this->tasks_buffer_parameters->name, $this->tasks_buffer_parameters->gitUrl, $this->tasks_buffer_parameters->gitBranch);
                $this->set_source_entity($platform->get_id());
                $this->save_logs($platform->get_logs());

                // Set task as ended with WARNING if ansible did not have a 0 (ok) return code
                // TODO (LATER) > Better error handling
                if($platform->get_ansible_status() === false) {
                    $this->end_warning();
                }
                else {
                    $this->end();
                }
                    
                break;
                
            case 'PLATFORM_PULL':
                echo "Doing PLATFORM_PULL\n";

                echo var_dump($this->tasks_buffer_parameters);

                $platform = Platform::task_pull($this->tasks_buffer_parameters->resourceId);
                $this->set_source_entity($this->tasks_buffer_parameters->resourceId);
                $this->save_logs($platform->get_logs());

                // Set task as ended with WARNING if ansible did not have a 0 (ok) return code
                // TODO (LATER) > Better error handling
                if($platform->get_ansible_status() === false) {
                    $this->end_warning();
                }
                else {
                    $this->end();
                }

                break;

                // -- Site related tasks
            case 'SITE_ADD':
                echo "Doing SITE_ADD\n";
                $site = Site::task_add(
                    $this->tasks_buffer_parameters->name, 
                    $this->tasks_buffer_parameters->platformId,
                    $this->tasks_buffer_parameters->domain,
                    $this->tasks_buffer_parameters->installProfileId, 
                    $this->tasks_buffer_parameters->language
                );
                $this->set_source_entity($site->get_id());
                    
                $this->save_logs($site->get_logs());

                if($site->get_ansible_status() === false) {
                    $this->end_warning();
                }
                else {
                    $this->end();
                }
                break;

            case 'SITE_RESET_PASSWORD':
                echo "Doing SITE_RESET_PASSWORD\n";
                $site = Site::task_reset_password($this->tasks_buffer_parameters->resourceId);
                $this->set_source_entity($site->get_id());
                    
                $this->save_logs($site->get_logs());

                if($site->get_ansible_status() === false) {
                    $this->end_warning();
                }
                else {
                    $this->end();
                }

                $result = new stdClass();
                $result->password_reset_url = $site->get_password_reset_url();

                $this->save_results(json_encode($result));

                break;

            case 'SITE_CLEAR_CACHE':
                echo "Doing SITE_CLEAR_CACHE\n";
                $site = Site::task_clear_cache($this->tasks_buffer_parameters->resourceId);
                $this->set_source_entity($site->get_id());
                    
                $this->save_logs($site->get_logs());

                if($site->get_ansible_status() === false) {
                    $this->end_warning();
                }
                else {
                    $this->end();
                }

                break;

            case 'SITE_DISABLE':
                echo "Doing SITE_DISABLE\n";
                $site = Site::task_disable($this->tasks_buffer_parameters->resourceId);
                $this->set_source_entity($site->get_id());
                    
                $this->save_logs($site->get_logs());

                if($site->get_ansible_status() === false) {
                    $this->end_warning();
                }
                else {
                    $this->end();
                }

                break;

            case 'SITE_ENABLE':
                echo "Doing SITE_ENABLE\n";
                $site = Site::task_enable($this->tasks_buffer_parameters->resourceId);
                $this->set_source_entity($site->get_id());
                    
                $this->save_logs($site->get_logs());

                if($site->get_ansible_status() === false) {
                    $this->end_warning();
                }
                else {
                    $this->end();
                }
    
                break;
                

            default:
                // Unkown task => Let's set it as "FAILED"
                $this->set_failed();
                break;
            }
        }
        catch (Exception $e) {
            //echo var_dump($e);
            echo "Task failed. Meh... -- ".get_class($e)." \n";
            echo "Error : ".$e->getMessage()."\n";
            var_dump($e->getTraceAsString());
            $this->set_failed();
        }

        // Ensure the task state is fully up to date
        $this->update();
    }

    /**
     * Save the task log into the database
     */
    function save_logs( String $logs ) : void
    {

        $query = 'UPDATE `Task` 
        SET
            logs = :logs
        WHERE id = :task_id';

        $stmt = DB::$pdo->prepare($query);

        $ret = $stmt->execute(
            ['logs' => $logs,
            'task_id' => $this->task_id ]
        );
        
    }

    /**
     * Save the task results into the database
     */
    function save_results( String $results ) : void
    {

        $query = 'UPDATE `Task` 
        SET
            results = :results
        WHERE id = :task_id';

        $stmt = DB::$pdo->prepare($query);

        $ret = $stmt->execute(
            ['results' => $results,
            'task_id' => $this->task_id ]
        );
        
    }
}
