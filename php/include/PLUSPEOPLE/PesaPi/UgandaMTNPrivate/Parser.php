<?php
/*	Copyright (c) 2015, PLUSPEOPLE Kenya Limited. 
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
		Based on examples provided by Humphrey William
 */
namespace PLUSPEOPLE\PesaPi\UgandaMTNPrivate;
use \PLUSPEOPLE\PesaPi\Base\Utility;

class Parser {
	public function dateInput($time) {
		$dt = \DateTime::createFromFormat("Y-m-d H:i:s", $time);
		return $dt->getTimestamp();
	}

	public function parse($input) {
		$result = array("SUPER_TYPE" => 0,
										"TYPE" => 0,
										"RECEIPT" => "",
										"TIME" => 0,
										"PHONE" => "",
										"NAME" => "",
										"ACCOUNT" => "",
										"STATUS" => "",
										"AMOUNT" => 0,
										"BALANCE" => 0,
										"NOTE" => "",
										"COST" => 0);


		/*
$input = "Y'ello. You have received
UGX 1000000.00 from 
ABACUS
INVESTMENTS LIMITED
COMMISSION
(256774656827) on your
mobile money account at
2015-05-10
13:35:12.Your new
balance:UGX
1068816.00.
";		
		*/
		
		// REFACTOR: should be split into subclasses
		if (strpos($input, "You have received") !== FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_IN;

			$temp = array();
			preg_match_all("/You have received[\s\n]+UGX ([0-9\.\,]+) from[\s\n]+([^\(]+)[\s\n]+\(([0-9]+)\) on your[\s\n]+mobile money account at[\s\n]+(\d{4}-\d\d-\d\d?)[\s\n]+(\d\d?:\d\d:\d\d)\.Your new[\s\n]+balance:UGX[\s\n]+([0-9\.\,]+)\./mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["TYPE"] = Transaction::UG_MTN_PRIVATE_PAYMENT_RECEIVED;

				$result["AMOUNT"] = Utility::numberInput($temp[1][0]);
				$result["NAME"] = $temp[2][0];
				$result["PHONE"] = $temp[3][0];
				$result["TIME"] = $this->dateInput($temp[4][0] . " " . $temp[5][0]);
				$result["BALANCE"] = Utility::numberInput($temp[6][0]);
				$result["RECEIPT"] = $result["TIME"]; // since no unique code is given we will use the timestamp				
			}


		} else {
			$result["SUPER_TYPE"] = Transaction::MONEY_NEUTRAL;
			$result["TYPE"] = Transaction::UG_MTN_PRIVATE_UNKOWN;
		}

		return $result;
	}

}

?>