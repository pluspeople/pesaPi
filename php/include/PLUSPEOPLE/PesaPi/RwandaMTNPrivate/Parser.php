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

		File originally by Michael Pedersen <kaal@pluspeople.dk>
		Thanks to Josh Handley for supplying information about MTN in Rwanda
 */
namespace PLUSPEOPLE\PesaPi\RwandaMTNPrivate;
use \PLUSPEOPLE\PesaPi\Base\Utility;
use \PLUSPEOPLE\PesaPi\Base\Transaction;


// I NEED MORE EXAMPLE SMS'S FROM RWANDA TO COMPLETE THIS!!!
class Parser {
	const PAYMENT_RECEIVED = 200;
	const UNKNOWN = 210;

	public function dateInput($time) {
		$dt = \DateTime::createFromFormat("j/n/y h:i A", $time);
		return $dt->getTimestamp();
	}


	public function parse($input) {
		$result = array("SUPER_TYPE" => 0,
										"RECEIPT" => "",
										"TIME" => 0,
										"PHONE" => "",
										"NAME" => "",
										"ACCOUNT" => "",
										"STATUS" => "",
										"AMOUNT" => 0,
										"BALANCE" => 0,
										"NOTE" => "",
										"COSTS" => 0);

		if (strpos($input, "You have received ") !== FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_IN;
			$result["TYPE"] = Parser::PAYMENT_RECEIVED;
		
			$temp = array();
			preg_match_all("/You have received Rwf\s+([0-9\.\,]+)\s+from\s+([0-9]+)\.\r?\nReason:\s+([^\r\n])\.\r?\nYour\s+balance\s+is\s+Rwf\s+([0-9\.\,]+)\./mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = ""; // NOT GOOD - NO RECEIPT NUMBER
				$result["AMOUNT"] = Utility::numberInput($temp[1][0]);
				$result["NAME"] = "";
				$result["ACCOUNT"] = $temp[3][0];
				$result["PHONE"] = $temp[2][0];
				$result["TIME"] = time();
				$result["BALANCE"] = Utility::numberInput($temp[4][0]);
			}

		} else {
			$result["SUPER_TYPE"] = Transaction::MONEY_NEUTRAL;
			$result["TYPE"] = PersonalParser::UNKNOWN;
		}

		return $result;
	}

}

?>