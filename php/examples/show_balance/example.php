<?php
set_include_path("../../local_include:../../include:" . get_include_path());
require_once("PLUSPEOPLE/autoload.php");

// Simple syncronisation example
$pesa = new PLUSPEOPLE\PesaPi\PesaPi();
print "Available balance: " . $pesa->availableBalance() . "\n";

?>