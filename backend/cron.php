<?php
/**
 * Dropfactory Backend - Main entry point
 * 
 * This file should be run by cron frequently to apply the changes requested
 * as tasks via the web interface
 * 
 * PHP version 8
 * 
 * @author  Ludovic Poujol <lpoujol@evolix.fr>
 * @author  Gregory Colpart <reg@evolix.fr>
 * @author  Evolix <info@evolix.fr>
 * @license TODO
 * @link    TODO
 */

/**
 * TODOS : 
 * * Locking system (ie: prevent two cron to be simulteanously running)
 * * Clean tasks stuck running ?
 */

declare(strict_types=1);

// Out of lazyness, manual include, shall be updated to PSR-4 Autoloader thingy
require 'lib/db.php';
require 'lib/task.php';
require 'lib/site.php';
require 'lib/platform.php';
require 'lib/ansible.php';


$CONFIG = parse_ini_file('./conf/config.ini', true);

if ($CONFIG === false) {
    echo "Couldn't read/parse ./conf/config.ini file";
    exit(1);
}

DB::connect($CONFIG);

// Fetch all new tasts waiting in the buffer queue
$q = DB::$pdo->query('SELECT * FROM `TaskBuffer`');
$waiting_tasks = $q->rowcount();

if ($waiting_tasks === 0) {
    echo "No tasks in queue\n";
    echo "Exiting...\n";
    exit(0);
}

echo "There are : ". $waiting_tasks." waiting to be handled\n";

foreach ($q as $value) {
    $task = new Task(
        $value['id'],
        $value['created_at'],
        $value['action'],
        $value['parameters'],
    );

    // Validate task parameters 
    $task->validate();
    
    // Transfer the task from table TaskBuffer to table Task
    $task->insert();
    $task->remove_from_buffer();

    // Do the task (and update its status)
    $task->do();
}
