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

require_once("../include/Configuration.php");
require_once("Database.php");
require_once("SlowTemplate.php");
require_once("WebUtility.php");
require_once("Payment.php");
require_once("PaymentFactory.php");

$slow = new SlowTemplate('template');
$slow->setTemplateFile('home2.tpl');
session_start();

//////////////////////////////////////////////////////////////////////////////
// handle the submission
if ($_SERVER["REQUEST_METHOD"] == 'POST') {
	if ($_POST['__VIEWSTATE'] == $_SESSION['VIEWSTATE']) {



	}
}

//////////////////////////////////////////////////////////////////////////////
// display the page
$view = WebUtility::viewstate(1476);
$_SESSION['VIEWSTATE'] = $view;

// Tariff's 
$tariffs = array('MFI Tariff 4', 'Unkown');

// page size
$pagesizes = array(20, 50, 100, 500);

// Search results
$results = PaymentFactory::factoryAll();
foreach ($results AS $result) {
	$slow->assign(array("RECEIPT" => $result->getReciept(),
											"TIME" => date("Y-m-d H:i:s", $result->getTime()),
											"DESCRIPTION" => "Payment received from " . $result->getPhonenumber() . " - " . $result->getName() . " Acc. " . $result->getAccount(),
											"STATUS" => "Completed",
											"AMOUNT" => number_format($result->getAmount(), 2, '.', ''),
											"BALANCE" => number_format($result->getPostBalance(), 2, '.', ''),
											"HASH" => "b142222a-59ab-2ef6-8e52-a027ca4edb21"
											));

	$slow->parse("Result");
}


$slow->assign(array("VIEWSTATE" => $view,
										"ORGANISATION" => "MpesaPi",
										"USERNAME" => "Test Testson",
										"LOGIN_TIME" => date("Y-m-d H:i:s"),
										"LAST_LOGIN_TIME" => date("Y-m-d H:i:s"),
										"ACCOUNT_NUMBER" => '32943321-11',
										"TARIFF" => $tariffs[1],
										"SEARCH_FROM" => date("Y-m-d H:i:s"),
										"SEARCH_FROM_DAY" => date("Y-m-d"),
										"SEARCH_FROM_TIME" => "00:00",
										"SEARCH_UNTIL" => date("Y-m-d H:i:s"),
										"SEARCH_UNTIL_DAY" => date("Y-m-d"),
										"SEARCH_UNTIL_TIME" => "23:59",
										"PAGE_SIZE_INDEX" => "0",
										"PAGE_SIZE" => $pagesizes[0],
										"CHECKED_ALL" => 'checked="checked"',
										"CHECKED_DECLINED" => '',
										"CHECKED_CANCELLED" => '',
										"CHECKED_EXPIRED" => '',
										"CHECKED_PENDING" => '',
										"CHECKED_COMPLETED" => '',
										"BALANCE_UPDATED" => date("Y-m-d H:i:s"),
										"CURRENT_BALANCE" => "           0.00",
										"UNCLEARED_FUNDS" => "           0.00",
										"RESERVED_FUNDS" => "           0.00",
										"AVAILABLE_FUNDS" => "           0.00",
										));


$slow->parse();
$slow->slowPrint();


?>