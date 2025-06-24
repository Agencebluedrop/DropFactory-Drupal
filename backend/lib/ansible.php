<?php
/**
 * Dropfactory Backend - Ansible Class
 * 
 * Represents an Ansible run
 * 
 * PHP version 8
 * 
 * @author  Ludovic Poujol <lpoujol@evolix.fr>
 * @author  Gregory Colpart <reg@evolix.fr>
 * @author  Evolix <info@evolix.fr>
 * @license TODO
 * @link    TODO
 */
class Ansible
{

    protected String $playbook_name;
    protected Bool   $run_check_mode = true;

    protected Array  $run_variables = array();


    protected stdClass $run_output;
    protected Int      $run_result_code = 0;

    /**
     * Instanciate a new Ansible object for a playbook execution
     * 
     * @param String $playbook_name The playbook file name (in ./src/ansible/)
     */
    function __construct(String $playbook_name)
    {
        $this->playbook_name = $playbook_name;
    }

    /**
     * Declare an additionnal variable to be added to the
     * The variables will be submited as an "extra-vars" file
     * 
     * @param String       $name  : The variable name in ansible
     * @param String|Array $value : The variable value 
     */
    function add_var(String $name, String|Array $value) : void
    {
        $this->run_variables[$name] = $value;
    }

    /**
     * Execute the ansible playbook
     */
    function run() : void
    {
        // putenv("ANSIBLE_CALLBACK_WHITELIST=json");
        putenv("ANSIBLE_CALLBACKS_ENABLED=json");
        putenv("ANSIBLE_STDOUT_CALLBACK=json");

        $tempfile_vars = tmpfile();
        $tempfile_vars_path = stream_get_meta_data($tempfile_vars)['uri']; // eg: /tmp/phpFx0513a 
        //fwrite($tempfile_vars, "{}");
        fwrite($tempfile_vars, json_encode($this->run_variables));

        $output = "";
        
        echo "Running asnible with the following command :\n";
        echo "   ansible-playbook --inventory ./ansible/hosts --extra-vars @$tempfile_vars_path ./ansible/$this->playbook_name \n";
        echo "Content of the extra-vars file :\n";
        echo "   ".file_get_contents($tempfile_vars_path)."\n";

        exec("ansible-playbook --inventory ./ansible/hosts --extra-vars @$tempfile_vars_path ./ansible/$this->playbook_name", $output, $this->run_result_code);

        $this->run_output = json_decode(implode("\n", $output));

        //var_dump( $this->run_output );
    }

    /**
     * Check if ansible return code was 0 (ok) or not
     * 
     * @return Bool : True if ansible exec returned 0, false otherwise
     */
    function is_okay() : Bool
    {
        return ($this->run_result_code === 0 ? true : false);
    }

    /**
     * Get the logs (ansible output)
     * 
     * @return stdClass A class containing the decoded json outputed by ansible
     */
    function get_logs() : stdClass
    {
        return $this->run_output;
    }
}