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
 * * Cap how many cron.php processes run at once
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

$task = Task::claim_next();

if ($task === null) {
    echo "No tasks in queue\n";
    echo "Exiting...\n";
    exit(0);
}

while ($task !== null) {
    $task->validate();
    $task->do();
    $task = Task::claim_next();
}
