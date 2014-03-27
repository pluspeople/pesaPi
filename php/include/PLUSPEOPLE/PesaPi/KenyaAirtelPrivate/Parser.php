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
		Based on examples provided by Humphrey William
 */
namespace PLUSPEOPLE\PesaPi\KenyaAirtelPrivate;
use \PLUSPEOPLE\PesaPi\Base\Utility;

class Parser {
	public function dateInput($time) {
		$dt = \DateTime::createFromFormat("j/n/Y h:i A", $time);
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

		// REFACTOR: should be split into subclasses
    // IMPORTANT Airtime is first to avoid clash with "received" wording or incomming money
		if (strpos($input, "You have received Airtime of") !== FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_OUT;

			$temp = array();
			preg_match_all("/Trans. ID: ([0-9]+)\. You have received Airtime of[\s\n]+([0-9\.\,]+)Ksh from your OWN account\./mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["TYPE"] = Transaction::KE_AIRTEL_PRIVATE_AIRTIME_YOU;
				$result["RECEIPT"] = $temp[1][0];
				$result["AMOUNT"] = Utility::numberInput($temp[2][0]);
				$result["NAME"] = "Airtel";
				$result["TIME"] = time();
			}

		} elseif (strpos($input, "You have received ") !== FALSE) {
			if (preg_match("/You have received [0-9]+\.00Ksh/mi", $input) === 1) {
				$result["SUPER_TYPE"] = Transaction::MONEY_IN;
				$result["TYPE"] = Transaction::KE_AIRTEL_PRIVATE_PAYMENT_RECEIVED;

				$temp = array();
				preg_match_all("/Trans. ID: ([0-9]+) You have received ([0-9\.\,]+)\.00Ksh from[\s\n]+([A-Z '\.]+)\. Your available[\s\n]+balance is ([0-9\.\,]+)\.00Ksh\./mi", $input, $temp);
				if (isset($temp[1][0])) {
					$result["RECEIPT"] = $temp[1][0];
					$result["AMOUNT"] = Utility::numberInput($temp[2][0]);
					$result["NAME"] = $temp[3][0];
					$result["TIME"] = time();
					$result["BALANCE"] = Utility::numberInput($temp[4][0]);
					$result["COST"] = -1; // NOT DONE
				}

			} elseif (preg_match("/You have received [0-9]+Ksh/mi", $input) === 1) {
				$result["SUPER_TYPE"] = Transaction::MONEY_IN;
				$result["TYPE"] = Transaction::KE_AIRTEL_PRIVATE_DEPOSIT;

				$temp = array();
				preg_match_all("/Trans. ID: ([0-9]+) You have received ([0-9\.\,]+)Ksh from[\s\n]+([A-Z0-9'\.]+)\. Your available balance is ([0-9]+)\.00Ksh\./mi", $input, $temp);
				if (isset($temp[1][0])) {
					$result["RECEIPT"] = $temp[1][0];
					$result["AMOUNT"] = Utility::numberInput($temp[2][0]);
					$result["NAME"] = $temp[3][0];
					$result["TIME"] = time();
					$result["BALANCE"] = Utility::numberInput($temp[4][0]);
					$result["COST"] = 0;
				}

			} else {
				$result["SUPER_TYPE"] = Transaction::MONEY_NEUTRAL;
				$result["TYPE"] = Transaction::KE_AIRTEL_PRIVATE_UNKOWN;
			}

		} elseif (preg_match("/You have sent [0-9\.\,]+\.00Ksh to[\s\n]+.+ in reference to/mi", $input) === 1) {
			$result["SUPER_TYPE"] = Transaction::MONEY_OUT;
			$result["TYPE"] = Transaction::KE_AIRTEL_PRIVATE_PAYBILL_PAID;
			
			$temp = array();
			preg_match_all("/Trans. ID: ([0-9]+) You have sent ([0-9\.\,]+)Ksh to[\s\n]+([A-Z ']+) in reference to ([A-Z ']+)\.[\s\n]+Your available balance is[\s\n]+([0-9]+)\.00Ksh\./mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[1][0];
				$result["AMOUNT"] = Utility::numberInput($temp[2][0]);
				$result["NAME"] = $temp[3][0];
				$result["ACCOUNT"] = $temp[4][0];
				$result["TIME"] = time();
				$result["BALANCE"] = Utility::numberInput($temp[5][0]);
			}

		} elseif (preg_match("/You[\s\n]+have sent [0-9\.\,]+\.00Ksh to/mi", $input) === 1) {
			$result["SUPER_TYPE"] = Transaction::MONEY_OUT;
			$result["TYPE"] = Transaction::KE_AIRTEL_PRIVATE_PAYMENT_SENT;

			$temp = array();
			preg_match_all("/Trans. ID: ([0-9]+) You[\s\n]+have sent ([0-9\.\,]+)\.00Ksh to ([A-Z ']+)\. Your available[\s\n]+balance is ([0-9]+)\.00Ksh\./mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[1][0];
				$result["AMOUNT"] = Utility::numberInput($temp[2][0]);
				$result["NAME"] = $temp[3][0];
				$result["TIME"] = time();
				$result["BALANCE"] = Utility::numberInput($temp[4][0]);
			}

		} elseif (strpos($input, "Your available bal. is Ksh") !== FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_NEUTRAL;
			$result["TYPE"] = Transaction::KE_AIRTEL_PRIVATE_BALANCE_REQUEST;

			$temp = array();
			preg_match_all("/Your available bal\. is Ksh([0-9\.\,]+)\.00\./mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["NAME"] = "Airtel";
				$result["TIME"] = time();
				$result["BALANCE"] = Utility::numberInput($temp[1][0]);
			}

		} else {
			$result["SUPER_TYPE"] = Transaction::MONEY_NEUTRAL;
			$result["TYPE"] = Transaction::KE_AIRTEL_PRIVATE_UNKOWN;
			$result["TIME"] = time();
		}

		return $result;
	}

}

?>