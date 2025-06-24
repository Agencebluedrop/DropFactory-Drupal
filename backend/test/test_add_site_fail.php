<?php
/**
 * Test/dev file - Create a site (but will fail)
 * This site will be named `bar` with the current UNIX Timestamp appended at the end.
 * 
 * This site creation is intended to fail as it references an unknown platform
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
            VALUES (NOW(),"SITE_ADD", :parameters)';

$stmt = DB::$pdo->prepare($query);

$stmt->execute(['parameters' => '{"name":"bar'.time().'","platformId":2, "domain": "bar'.time().'.example.fr", "installProfileId":2,"language":"FR"}']);
