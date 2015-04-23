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
		Contributions by Moris M <morrisauk@gmail.com>
 */
namespace PLUSPEOPLE\PesaPi\MpesaPrivate;
use \PLUSPEOPLE\PesaPi\Base\Utility;

class PersonalParser {
	public function dateInput($time) {
		$dt = \DateTime::createFromFormat("j/n/y h:i A", $time);
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
			preg_match_all("/([A-Z0-9]+) Confirmed\.[\s\n]*You have received Ksh([0-9\.\,]+00) from[\s\n]+([0-9A-Z '\.]+) ([0-9]+)[\s\n]*on (\d\d?\/\d\d?\/\d\d) at (\d\d?:\d\d [AP]M)[\s\n]*New M-PESA balance is Ksh([0-9\.\,]+00)/mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["TYPE"] = Transaction::MPESA_PRIVATE_PAYMENT_RECEIVED;
				$result["RECEIPT"] = $temp[1][0];
				$result["AMOUNT"] = Utility::numberInput($temp[2][0]);
				$result["NAME"] = $temp[3][0];
				$result["PHONE"] = $temp[4][0];
				$result["TIME"] = $this->dateInput($temp[5][0] . " " . $temp[6][0]);
				$result["BALANCE"] = Utility::numberInput($temp[7][0]);

			} else {
				preg_match_all("/([A-Z0-9]+) Confirmed\.[\s\n]*You have received Ksh([0-9\.\,]+00) from[\s\n]+([0-9]+) - ([A-Z '\.]+) [\s\n]*on (\d\d?\/\d\d?\/\d\d) at (\d\d?:\d\d [AP]M)[\s\n]*New M-PESA balance is Ksh([0-9\.\,]+00)/mi", $input, $temp);
				if (isset($temp[1][0])) {
					$result["TYPE"] = Transaction::MPESA_PRIVATE_B2C_RECEIVED;
					$result["RECEIPT"] = $temp[1][0];
					$result["AMOUNT"] = Utility::numberInput($temp[2][0]);
					$result["ACCOUNT"] = $temp[3][0];
					$result["NAME"] = $temp[4][0];
					$result["TIME"] = $this->dateInput($temp[5][0] . " " . $temp[6][0]);
					$result["BALANCE"] = Utility::numberInput($temp[7][0]);
				}
			}

		} elseif (strpos($input, "received from") !== FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_IN;
			$result["TYPE"] = Transaction::MPESA_PRIVATE_BUYGOODS_RECEIVED;
			
			$temp = array();
			preg_match_all("/([A-Z0-9]+) Confirmed\.[\s\n]*on (\d\d?\/\d\d?\/\d\d) at (\d\d?:\d\d [AP]M)[\s\n]*Ksh([0-9\.\,]+00) received from[\s\n]+([0-9]+) ([0-9A-Z '\.]+)[\s\n]*New Account balance is Ksh([0-9\.\,]+00)/mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[1][0];
				$result["AMOUNT"] = Utility::numberInput($temp[4][0]);
				$result["NAME"] = trim($temp[6][0]);
				$result["TIME"] = $this->dateInput($temp[2][0] . " " . $temp[3][0]);
				$result["PHONE"] = $temp[5][0];
				$result["BALANCE"] = Utility::numberInput($temp[7][0]);
			}

		} elseif (preg_match("/sent to .+ for account/", $input) > 0) {
			$result["SUPER_TYPE"] = Transaction::MONEY_OUT;
			$result["TYPE"] = Transaction::MPESA_PRIVATE_PAYBILL_PAID;

			$temp = array();
			preg_match_all("/([A-Z0-9]+) Confirmed\.[\s\n]*Ksh([0-9\.\,]+) sent to[\s\n]*(.+)[\s\n]*for account (.+)[\s\n]*on (\d\d?\/\d\d?\/\d\d) at (\d\d?:\d\d [AP]M)[\s\n]*New M-PESA balance is Ksh([0-9\.\,]+00)/mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[1][0];
				$result["AMOUNT"] = Utility::numberInput($temp[2][0]);
				$result["NAME"] = $temp[3][0];
				$result["ACCOUNT"] = $temp[4][0];
				$result["TIME"] = $this->dateInput($temp[5][0] . " " . $temp[6][0]);
				$result["BALANCE"] = Utility::numberInput($temp[7][0]);
			}

		} elseif (preg_match("/Ksh[0-9\.\,]+ paid to /", $input) > 0) {
			$result["SUPER_TYPE"] = Transaction::MONEY_OUT;
			$result["TYPE"] = Transaction::MPESA_PRIVATE_BUY_GOODS;

			$temp = array();
			preg_match_all("/([A-Z0-9]+) Confirmed\.[\s\n]*Ksh([0-9\.\,]+) paid to[\s\n]*([.]+)[\s\n]*on (\d\d?\/\d\d?\/\d\d) at (\d\d?:\d\d [AP]M)[\s\n]*New M-PESA balance is Ksh([0-9\.\,]+00)/mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[1][0];
				$result["AMOUNT"] = Utility::numberInput($temp[2][0]);
				$result["NAME"] = $temp[3][0];
				$result["TIME"] = $this->dateInput($temp[4][0] . " " . $temp[5][0]);
				$result["BALANCE"] = Utility::numberInput($temp[6][0]);
			}

		} elseif (preg_match("/sent to .+ on/", $input) > 0) {
			$result["SUPER_TYPE"] = Transaction::MONEY_OUT;
			$result["TYPE"] = Transaction::MPESA_PRIVATE_PAYMENT_SENT;

			$temp = array();
			preg_match_all("/([A-Z0-9]+) Confirmed\.[\s\n]*Ksh([0-9\.\,]+00) sent to ([0-9A-Z '\.]+) ([0-9]+) on (\d\d?\/\d\d?\/\d\d) at (\d\d?:\d\d [AP]M)[\s\n]*New M-PESA balance is Ksh([0-9\.\,]+00)/mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[1][0];
				$result["AMOUNT"] = Utility::numberInput($temp[2][0]);
				$result["NAME"] = $temp[3][0];
				$result["PHONE"] = $temp[4][0];
				$result["TIME"] = $this->dateInput($temp[5][0] . " " . $temp[6][0]);
				$result["BALANCE"] = Utility::numberInput($temp[7][0]);
			}

		} elseif (preg_match("/Give Ksh[0-9\.\,]+ cash to/", $input) > 0) {
			$result["SUPER_TYPE"] = Transaction::MONEY_IN;
			$result["TYPE"] = Transaction::MPESA_PRIVATE_DEPOSIT;
			
			$temp = array();
			preg_match_all("/([A-Z0-9]+) Confirmed\.[\s\n]*on (\d\d?\/\d\d?\/\d\d) at (\d\d?:\d\d [AP]M)[\s\n]*Give Ksh([0-9\.\,]+00) cash to (.+)[\s\n]*New M-PESA balance is Ksh([0-9\.\,]+00)/mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[1][0];
				$result["AMOUNT"] = Utility::numberInput($temp[4][0]);
				$result["NAME"] = $temp[5][0];
				$result["TIME"] = $this->dateInput($temp[2][0] . " " . $temp[3][0]);
				$result["BALANCE"] = Utility::numberInput($temp[6][0]);
			}

		} elseif (preg_match("/Withdraw Ksh[0-9\.\,]+ from/", $input) > 0) {
			$result["SUPER_TYPE"] = Transaction::MONEY_OUT;
			$result["TYPE"] = Transaction::MPESA_PRIVATE_WITHDRAW;

			$temp = array();
			preg_match_all("/([A-Z0-9]+) Confirmed\.[\s\n]*on (\d\d?\/\d\d?\/\d\d) at (\d\d?:\d\d [AP]M)[\s\n]*Withdraw Ksh([0-9\.\,]+) from[\s\n]*(.+)[\s\n]*New M-PESA balance is Ksh([0-9\.\,]+00)/mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[1][0];
				$result["AMOUNT"] = Utility::numberInput($temp[4][0]);
				$result["NAME"] = $temp[5][0];
				$result["TIME"] = $this->dateInput($temp[2][0] . " " . $temp[3][0]);
				$result["BALANCE"] = Utility::numberInput($temp[6][0]);
			}

		} elseif (preg_match("/Ksh[0-9\.\,]+ withdrawn from/", $input) > 0) {
			$result["SUPER_TYPE"] = Transaction::MONEY_OUT;
			$result["TYPE"] = Transaction::MPESA_PRIVATE_WITHDRAW_ATM;

			$temp = array();
			preg_match_all("/([A-Z0-9]+) Confirmed[\s\n]*on (\d\d?\/\d\d?\/\d\d) at (\d\d?:\d\d [AP]M).[\s\n]*Ksh([0-9\.\,]+) withdrawn from (\d+) - AGENT ATM\.[\s\n]*New M-PESA balance is Ksh([0-9\.\,]+00)/mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[1][0];
				$result["AMOUNT"] = Utility::numberInput($temp[4][0]);
				$result["NAME"] = $temp[5][0];
				$result["TIME"] = $this->dateInput($temp[2][0] . " " . $temp[3][0]);
				$result["BALANCE"] = Utility::numberInput($temp[6][0]);
			}

		} elseif (preg_match("/You bought Ksh[0-9\.\,]+ of airtime on/", $input) > 0) {
			$result["SUPER_TYPE"] = Transaction::MONEY_OUT;
			$result["TYPE"] = Transaction::MPESA_PRIVATE_AIRTIME_YOU;

			$temp = array();
			preg_match_all("/([A-Z0-9]+) confirmed\.[\s\n]*You bought Ksh([0-9\.\,]+00) of airtime on (\d\d?\/\d\d?\/\d\d) at (\d\d?:\d\d [AP]M)[\s\n]*New M-PESA balance is Ksh([0-9\.\,]+00)/mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[1][0];
				$result["AMOUNT"] = Utility::numberInput($temp[2][0]);
				$result["NAME"] = "Safaricom";
				$result["TIME"] = $this->dateInput($temp[3][0] . " " . $temp[4][0]);
				$result["BALANCE"] = Utility::numberInput($temp[5][0]);
			}

		} elseif (preg_match("/You bought Ksh[0-9\.\,]+ of airtime for (\d+) on/", $input) > 0) {
			$result["SUPER_TYPE"] = Transaction::MONEY_OUT;
			$result["TYPE"] = Transaction::MPESA_PRIVATE_AIRTIME_OTHER;

			$temp = array();
			preg_match_all("/([A-Z0-9]+) confirmed\.[\s\n]*You bought Ksh([0-9\.\,]+) of airtime for (\d+) on (\d\d?\/\d\d?\/\d\d) at (\d\d?:\d\d [AP]M)[\s\n]*New M-PESA balance is Ksh([0-9\.\,]+00)/mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[1][0];
				$result["AMOUNT"] = Utility::numberInput($temp[2][0]);
				$result["NAME"] = $temp[3][0];
				$result["TIME"] = $this->dateInput($temp[4][0] . " " . $temp[5][0]);
				$result["BALANCE"] = Utility::numberInput($temp[6][0]);
			}

		} elseif (strpos($input, "from your M-Shwari account") !== FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_IN;
			$result["TYPE"] = Transaction::MPESA_PRIVATE_FROM_MSHWARI;

			$temp = array();
			preg_match_all("/([A-Z0-9]+) Confirmed\.[\s\n]*You have transferred Ksh([0-9\.\,]+00)[\s\n]*from your M-Shwari account[\s\n]*on (\d\d?\/\d\d?\/\d\d) at (\d\d?:\d\d [AP]M)\.[\s\n]*M-Shwari balance is Ksh[0-9\.\,]+\.[\s\n]*M-PESA balance is Ksh([0-9\.\,]+00)/mi", $input, $temp);
			$result["RECEIPT"] = $temp[1][0];
			$result["AMOUNT"] = Utility::numberInput($temp[2][0]);
			$result["NAME"] = "M-Shwari";
			$result["TIME"] = $this->dateInput($temp[3][0] . " " . $temp[4][0]);
			$result["BALANCE"] = Utility::numberInput($temp[5][0]);

		} elseif (strpos($input, "transferred to M-Shwari account") !== FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_OUT;
			$result["TYPE"] = Transaction::MPESA_PRIVATE_TO_MSHWARI;

			$temp = array();
			preg_match_all("/([A-Z0-9]+) Confirmed\.[\s\n]*Ksh([0-9\.\,]+00) transferred to M-Shwari account[\s\n]*on (\d\d?\/\d\d?\/\d\d) at (\d\d?:\d\d [AP]M)\.[\s\n]*M-PESA balance is Ksh([0-9\.\,]+00)/mi", $input, $temp);
			$result["RECEIPT"] = $temp[1][0];
			$result["AMOUNT"] = Utility::numberInput($temp[2][0]);
			$result["NAME"] = "M-Shwari";
			$result["TIME"] = $this->dateInput($temp[3][0] . " " . $temp[4][0]);
			$result["BALANCE"] = Utility::numberInput($temp[5][0]);

		} elseif (strpos($input, "Your M-PESA balance was") !== FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_NEUTRAL;
			$result["TYPE"] = Transaction::MPESA_PRIVATE_BALANCE_REQUEST;

			$temp = array();
			preg_match_all("/([A-Z0-9]+) Confirmed\.[\s\n]*Your M-PESA balance was Ksh([0-9\.\,]+00)[\s\n]*on (\d\d?\/\d\d?\/\d\d) at (\d\d?:\d\d [AP]M)/mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[1][0];
				$result["TIME"] = $this->dateInput($temp[3][0] . " " . $temp[4][0]);
				$result["BALANCE"] = Utility::numberInput($temp[2][0]);
			}

		} elseif (strpos($input, "Failed.") !== FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_NEUTRAL;
			$result["TYPE"] = Transaction::MPESA_PRIVATE_ERROR;
			$result["TIME"] = time();
			$result["NOTE"] = $input;

		} else {
			$result["SUPER_TYPE"] = Transaction::MONEY_NEUTRAL;
			$result["TYPE"] = Transaction::MPESA_PRIVATE_UNKNOWN;
		}
		$result["COST"] = ChargeCalculator::calculateCost($result["TYPE"], $result["TIME"], $result["AMOUNT"]);

		return $result;
	}

}

?>