<?php
/*	Copyright (c) 2011, PLUSPEOPLE Kenya Limited. 
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
 */
namespace PLUSPEOPLE\Pesapi\simulator;

require_once("../../include/Configuration.php");
require_once("SlowTemplate.php");
require_once("WebUtility.php");
require_once("Database.php");
require_once("Payment.php");

$slow = new SlowTemplate('template/admin');
$slow->setTemplateFile('index.tpl');
session_start();

//////////////////////////////////////////////////////////////////////////////
// handle the submission
if ($_SERVER["REQUEST_METHOD"] == 'POST') {
	if (isset($_POST["ok"])) {
		$reciept = "BCXY" . rand(1000, 9999); // need to be more random
		$payment = Payment::createNew($reciept, Payment::TYPE_PAYMENT_RECIEVED);
		if (is_object($payment)) {
			$amount = numberInput($_POST["amount"]);
			$payment->setTime(dateInput($_POST["day"] . " " . $_POST["time"]));
			$payment->setPhonenumber($_POST["phone"]);
			$payment->setName($_POST["name"]);
			$payment->setAccount(rand(1000,9999));
			$payment->setStatus(Payment::STATUS_COMPLETED);
			$payment->setAmount($amount);
			$payment->setPostBalance($amount);
			$payment->update();

			print "Payment created";
		}
	}
}

//////////////////////////////////////////////////////////////////////////////
// display the page
$slow->assign(array("DAY" => date("d-m-Y"),
										"TIME" => date("H:s")
										));


$slow->parse();
$slow->slowPrint();

function numberInput($input) {
	$input = trim($input);
	$amount = 0;
	
	if (preg_match("/^[0-9,]+$/", $input)) {
		$amount = 100 * (int)str_replace(',', '', $input);
	} elseif (preg_match("/^[0-9,]+\.[0-9]$/", $input)) {
		$amount = 10 * (int)str_replace(array('.', ','), '', $input);
	} elseif (preg_match("/^[0-9,]*\.[0-9][0-9]$/", $input)) {
		$amount = (int)str_replace(array('.', ','), '', $input);
	} else {
		$amount = (int)$input;
	}
	return $amount;
}
function dateInput($input) {
	$timeStamp = strtotime($input);
	if ($timeStamp != FALSE) {
		return $timeStamp;
	}
	return 0;
}

?>