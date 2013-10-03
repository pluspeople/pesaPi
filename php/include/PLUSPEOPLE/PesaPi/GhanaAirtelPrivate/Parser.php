<?php
/*	Copyright (c) 2011-2013, PLUSPEOPLE Kenya Limited. 
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
		Thanks to Henry Addo for supplying information about Airtel Money in Ghana
 */
namespace PLUSPEOPLE\PesaPi\GhanaAirtelPrivate;
use \PLUSPEOPLE\PesaPi\Base\Utility;

// WE NEED MORE EXAMPLE SMS'S FROM GHANA TO COMPLETE THIS!!!
class Parser {

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
										"COST" => 0);

		if (strpos($input, "You have received ") !== FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_IN;
			$result["TYPE"] = Transaction::GH_AIRTEL_PAYMENT_RECEIVED;
		
			$temp = array();
			preg_match_all("/Trans\.\s+ID:\s+([0-9]+)\s+You have received ([0-9\.\,]+)GHS\s+from([0-9]+)\.\s+Your available\s+balance\s+is\s+([0-9\.\,]+)GHS/mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[1][0];
				$result["AMOUNT"] = Utility::numberInput($temp[2][0]);
				$result["NAME"] = "";
				$result["PHONE"] = $temp[3][0];
				$result["TIME"] = time();
				$result["BALANCE"] = Utility::numberInput($temp[4][0]);
			}

		} elseif (strpos($input, "You have sent ") !== FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_OUT;
			$result["TYPE"] = Transaction::GH_AIRTEL_PAYMENT_SENT;
		
			$temp = array();
			preg_match_all("/Trans\.\s+ID:\s+([0-9]+)\s+You have sent ([0-9\.\,]+)GHS\s+to([0-9]+)\.\s+Your available\s+balance\s+is\s+([0-9\.\,]+)GHS/mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[1][0];
				$result["AMOUNT"] = Utility::numberInput($temp[2][0]);
				$result["NAME"] = "";
				$result["PHONE"] = $temp[3][0];
				$result["TIME"] = time();
				$result["BALANCE"] = Utility::numberInput($temp[4][0]);
			}

		} elseif (strpos($input, "You have received Airtime of") != FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_OUT; // NOT CERTAIN - someone else may be giving us airtime.
			$result["TYPE"] = Transaction::GH_AIRTEL_AIRTIME;
		
			$temp = array();
			preg_match_all("/Trans\.\s+ID:\s+([0-9]+)\s+You have received Airtime of\s+GHS\s+([0-9\.\,]+)\s+from([0-9]+)\./mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[1][0];
				$result["AMOUNT"] = Utility::numberInput($temp[2][0]);
				$result["NAME"] = "";
				$result["PHONE"] = $temp[3][0];
				$result["TIME"] = time();
				$result["BALANCE"] = -1; // Unkown
			}

		} elseif (strpos($input, "you have paid") != FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_OUT;
			$result["TYPE"] = Transaction::GH_AIRTEL_PURCHASE;

			$temp = array();
			preg_match_all("/Trans\s*ID:\s+([0-9]+)\s+Transaction successful, you have paid ([0-9\.\,]+)GHS\s+to reference code ([0-9]+)/mi", $example, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[1][0];
				$result["AMOUNT"] = Utility::numberInput($temp[2][0]);
				$result["ACCOUNT"] = $temp[3][0];
				$result["TIME"] = time();
				$result["BALANCE"] = -1; // Unkown
			}

		} else {
			$result["SUPER_TYPE"] = Transaction::MONEY_NEUTRAL;
			$result["TYPE"] = Transaction::GH_AIRTEL_UNKNOWN;
		}

		return $result;
	}

}

?>