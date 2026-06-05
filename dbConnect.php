<?php

try {
    $dbConnect = new PDO(
        'mysql:host=localhost;dbname=musicOnline',
        'user1',
        'user123',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}
catch (PDOException $e)
{
    die("Connection failed");
}