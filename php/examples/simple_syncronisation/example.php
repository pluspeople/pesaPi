<?php
set_include_path("../../include:" . get_include_path());
require_once("PLUSPEOPLE/autoload.php");

// Simple syncronisation example
$mpesa = new PLUSPEOPLE\Pesapi\Pesapi();
$mpesa->forceSyncronisation();

?>