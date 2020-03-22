<?php

// Test SSH to a device.

require __DIR__.'/vendor/autoload.php';

use phpseclib\Net\SSH2;

$host = '10.0.0.1';
$user = 'admin';
$pass = 'admin';

$ssh = new SSH2($host);
if (! $ssh->login($user, $pass)) {
    exit('Login Failed');
}

echo $ssh->exec('pwd');
