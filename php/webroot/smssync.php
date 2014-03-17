<?php
/*	Copyright (c) 2014, PLUSPEOPLE Kenya Limited. 
		All rights reserved.

		Redistribution and use in source and binary forms, with or without
		modification, are permitted provided that the following conditions
		are met:
		1. Redistributions of source code must retain the above copyright
		   notice, this list of conditions and the following disclaimer.
		2. Redistributions in binary form must reproduce the above copyright
		   notice, this list of conditions and the following disclaimer in the
		   documentation and/or other materials provided with the distribution.
		3. Neither the name of PLUSPEOPLE nor the names of its contributors 
		   may be used to endorse or promote products derived from this software 
		   without specific prior written permission.
		
		THIS SOFTWARE IS PROVIDED BY THE REGENTS AND CONTRIBUTORS ``AS IS'' AND
		ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
		IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
		ARE DISCLAIMED.  IN NO EVENT SHALL THE REGENTS OR CONTRIBUTORS BE LIABLE
		FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
		DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
		OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
		HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
		LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
		OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
		SUCH DAMAGE.

		File originally by Michael Pedersen <kaal@pluspeople.dk>
 */
set_include_path("../local_include:../include:" . get_include_path());
require_once("PLUSPEOPLE/autoload.php");

use \PLUSPEOPLE\PesaPi\Base\Account;

// Define constants
$payloadSuccess = '{ payload: { success: "true" } }';
$payloadFailure = '{ payload: { success: "false" } }';


// import payload/variables from smssync
$from = @$_POST["from"];
$message = @$_POST["message"];
$secret = @$_POST["secret"];
$sent_timestamp = @$_POST["sent_timestamp"];

$config = PLUSPEOPLE\PesaPi\Configuration::instantiate();
if (!is_object($config)) {
	print $payloadFailure;
	exit();
}

$pesa = new PLUSPEOPLE\PesaPi\PesaPi();
$identifier = $_GET["identifier"];
$accounts = $pesa->getAccount($identifier);

if (!isset($accounts[0])) {
	print $payloadFailure;
	exit();
}
$account = $accounts[0]; 
$settings = $account->getSettings();

if ($settings["SYNC_SECRET"] == "" OR $settings["SYNC_SECRET"] != $secret) {
	print $payloadFailure;
	exit();
}


switch ($account->getType()) {
case Account::TANZANIA_MPESA_PRIVATE:
case Account::MPESA_PRIVATE:
	if ($from != "MPESA") {
		print $payloadSuccess;
		exit();
	}
	break;
}
	

$transaction = $account->importTransaction($message);
if (is_object($transaction)) {
	$transaction->setNote($message);
	$transaction->update();
	print $payloadSuccess;
	exit();
}
print $payloadFailure;





?>