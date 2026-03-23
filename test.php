<?php
echo "MYSQLHOST: " . getenv('MYSQLHOST') . "<br>";
echo "MYSQLUSER: " . getenv('MYSQLUSER') . "<br>";
echo "MYSQLDATABASE: " . getenv('MYSQLDATABASE') . "<br>";
echo "MYSQLPORT: " . getenv('MYSQLPORT') . "<br>";
echo "MYSQLPASSWORD: " . (getenv('MYSQLPASSWORD') ? 'SET' : 'NOT SET') . "<br>";
?>