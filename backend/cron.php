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

if (!acquire_cron_slot($CONFIG)) {
    echo "max_parallel_crons reached\n";
    echo "Exiting...\n";
    exit(0);
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

/**
 * Take one of the cron.N.lock slots, or return false when all are held.
 *
 * The handle is kept in a static until this process ends. A local handle
 * would close on return and free the slot while Ansible is still running.
 * A full set of slots is a normal wait. A directory or file that cannot be
 * opened is a fault.
 */
function acquire_cron_slot(array $config): bool
{
    static $slot = null;

    $limit = cron_slot_limit($config);
    $directory = '/var/lock/dropfactory';

    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        echo "Could not create {$directory}\n";
        exit(1);
    }

    for ($slotNumber = 1; $slotNumber <= $limit; $slotNumber++) {
        $path = $directory . '/cron.' . $slotNumber . '.lock';
        $handle = fopen($path, 'c');
        if ($handle === false) {
            echo "Could not open {$path}\n";
            exit(1);
        }
        if (flock($handle, LOCK_EX | LOCK_NB)) {
            $slot = $handle;

            return true;
        }
        fclose($handle);
    }

    return false;
}

/**
 * Read max_parallel_crons. A missing key means 3.
 */
function cron_slot_limit(array $config): int
{
    if (!isset($config['cron']['max_parallel_crons'])) {
        return 3;
    }

    $raw = $config['cron']['max_parallel_crons'];
    if ((is_int($raw) && $raw > 0) || (is_string($raw) && ctype_digit($raw) && (int) $raw > 0)) {
        return (int) $raw;
    }

    echo "max_parallel_crons must be a positive integer\n";
    exit(1);
}
