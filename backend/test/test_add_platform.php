<?php
/**
 * Test/dev file - Create a platform
 * This platform will be name `foo` with the current UNIX Timestamp appended at the end
 * 
 * TODO : Change GIT URL to a public repository
 */

declare(strict_types=1);

include '../lib/db.php';

$CONFIG = parse_ini_file('../conf/config.ini', true);

if( $CONFIG === false){
    echo "Couldn't read/parse ../conf/config.ini file";
    exit(1);
}

DB::connect($CONFIG);

$query = 'INSERT INTO `TaskBuffer`  (created_at, action, parameters) 
            VALUES (NOW(),"PLATFORM_ADD", :parameters)';


$stmt = DB::$pdo->prepare($query);

$stmt->execute(['parameters' => '{"name":"foo'.time().'","gitUrl":"git@gitea.evolix.org:lpoujol/df-drupal10.git","gitBranch":"main"}']);

