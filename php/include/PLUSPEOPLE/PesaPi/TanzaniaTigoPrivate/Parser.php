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
namespace PLUSPEOPLE\PesaPi\TanzaniaTigoPrivate;
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
		if (strpos($input, "You have received") !== FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_IN;

			$temp = array();
			preg_match_all("/New balance is Tsh ([0-9\.\,]+)[\s\n]+You have received Tsh ([0-9\.\,]+)[\s\n]+from ([A-Z '\.]+),[\s\n]+([0-9]+)\. (\d\d?\/\d\d?\/\d{4}) (\d\d?:\d\d [AP]M)\; with TxnId:[\s\n]+([^.]+\.[^.]+\.[^.]+)\./mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["TYPE"] = Transaction::TZ_TIGO_PRIVATE_PAYMENT_RECEIVED;
				$result["RECEIPT"] = $temp[7][0];
				$result["AMOUNT"] = Utility::numberInput($temp[2][0]);
				$result["NAME"] = $temp[3][0];
				$result["PHONE"] = $temp[4][0];
				$result["TIME"] = $this->dateInput($temp[5][0] . " " . $temp[6][0]);
				$result["BALANCE"] = Utility::numberInput($temp[1][0]);
			}

		} elseif (strpos($input, "Money sent successfully to") !== FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_OUT;
			$result["TYPE"] = Transaction::TZ_TIGO_PRIVATE_PAYMENT_SENT;

			$temp = array();
			preg_match_all("/New balance is Tsh ([0-9\.\,]+)[\s\n]+Money sent successfully to[\s\n]+([A-Z '\.]+), ([0-9]+)\.[\s\n]+Amount: Tsh ([0-9\.\,]+) Fee: Tsh ([0-9\.\,]+)TxnID:[\s\n]+([^.]+\.[^.]+\.[^.]+)\./mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[6][0];
				$result["AMOUNT"] = Utility::numberInput($temp[4][0]);
				$result["NAME"] = $temp[2][0];
				$result["PHONE"] = $temp[3][0];
				$result["TIME"] = time();
				$result["BALANCE"] = Utility::numberInput($temp[1][0]);
				$result["COST"] = Utility::numberInput($temp[5][0]);
			}

		} elseif (strpos($input, "Cash-In of Tsh") !== FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_IN;
			$result["TYPE"] = Transaction::TZ_TIGO_PRIVATE_DEPOSIT;
			
			$temp = array();
			preg_match_all("/New balance is Tsh ([0-9\.\,]+)[\s\n]+Cash-In of Tsh ([0-9\.\,]+)[\s\n]+successful\. Agent ([A-Z '\.]+)\.[\s\n]+TxnID:[\s\n]+([^.]+\.[^.]+\.[^.]+)\./mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[4][0];
				$result["AMOUNT"] = Utility::numberInput($temp[2][0]);
				$result["NAME"] = $temp[3][0];
				$result["TIME"] = time();
				$result["BALANCE"] = Utility::numberInput($temp[1][0]);
			}

		} elseif (strpos($input, "Cash-Out to") !== FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_OUT;
			$result["TYPE"] = Transaction::TZ_TIGO_PRIVATE_WITHDRAW;

			$temp = array();
			preg_match_all("/New balance is Tsh ([0-9\.\,]+)[\s\n]+Cash-Out to ([A-Z '\.]+) was successful\. Amount Tsh[\s\n]+([0-9\.\,]+) Charges Tsh ([0-9\.\,]+)TxnID[\s\n]+([^.]+\.[^.]+\.[^.]+)\./mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[5][0];
				$result["AMOUNT"] = Utility::numberInput($temp[3][0]);
				$result["NAME"] = $temp[2][0];
				$result["TIME"] = time();
				$result["BALANCE"] = Utility::numberInput($temp[1][0]);
				$result["COST"] = Utility::numberInput($temp[4][0]);
			}

		} elseif (strpos($input, "recharge request is successful") !== FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_OUT;
			$result["TYPE"] = Transaction::TZ_TIGO_PRIVATE_AIRTIME_YOU;

			$temp = array();
			preg_match_all("/New balance is Tsh ([0-9\.\,]+)[\s\n]+Your[\s\n]+recharge request is successful[\s\n]+for amount Tsh ([0-9\.\,]+)[\s\n]+TxnId :[\s\n]+([^.]+\.[^.]+\.[^.]+)\./mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[3][0];
				$result["AMOUNT"] = Utility::numberInput($temp[2][0]);
				$result["NAME"] = "Tigo";
				$result["TIME"] = time();
				$result["BALANCE"] = Utility::numberInput($temp[1][0]);
			}

		} elseif (strpos($input, "Bill Transaction has been sent") !== FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_OUT;
			$result["TYPE"] = Transaction::TZ_TIGO_PRIVATE_PAYBILL_PAID;

			$temp = array();
			preg_match_all("/Bill Transaction has been sent[\s\n]+to ([A-Z '\.]+)\.Please wait for[\s\n]+confirmation TxnId:[\s\n]+([^.]+\.[^.]+\.[^,]+)\, Bill[\s\n]+Number:([0-9]+),[\s\n]+transaction amount : ([0-9\.\,]+)[\s\n]+Tsh,new balance :([0-9\.\,]+) Tsh,[\s\n]+Company ([A-Z '\.]+)\./mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[2][0];
				$result["AMOUNT"] = Utility::numberInput($temp[4][0]);
				$result["ACCOUNT"] = $temp[3][0];
				$result["NAME"] = $temp[1][0] != $temp[6][0] ? $temp[6][0] . " - " . $temp[1][0] : $temp[1][0];
				$result["TIME"] = time();
				$result["BALANCE"] = Utility::numberInput($temp[5][0]);
			}

		} elseif (strpos($input, "Bank payment successfull. The details are") !== FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_OUT;
			$result["TYPE"] = Transaction::TZ_TIGO_PRIVATE_DEPOSIT_BANK;

			$temp = array();
			preg_match_all("/Bank payment successfull\. The details are : TxnId:[\s\n]+([^.]+\.[^.]+\.[^,]+), Ref[\s\n]+Number:([0-9]+),[\s\n]+transaction amount : ([0-9\.\,]+)[\s\n]+Tsh , charges: ([0-9\.\,]+) Tsh,new[\s\n]+balance :([0-9\.\,]+) Tsh, Bank Name :[\s\n]+([A-Z '\.]+)/mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[1][0];
				$result["AMOUNT"] = Utility::numberInput($temp[3][0]);
				$result["ACCOUNT"] = $temp[2][0];
				$result["NAME"] = $temp[6][0];
				$result["TIME"] = time();
				$result["BALANCE"] = Utility::numberInput($temp[5][0]);
				$result["COST"] = Utility::numberInput($temp[4][0]);
			}

		} else {
			$result["SUPER_TYPE"] = Transaction::MONEY_NEUTRAL;
			$result["TYPE"] = Transaction::TZ_TIGO_PRIVATE_UNKOWN;
		}

		return $result;
	}

}

?>