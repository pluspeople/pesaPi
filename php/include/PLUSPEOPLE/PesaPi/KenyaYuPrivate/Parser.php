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
 */
namespace PLUSPEOPLE\PesaPi\KenyaYUPrivate;
use \PLUSPEOPLE\PesaPi\Base\Utility;
use \PLUSPEOPLE\PesaPi\Base\Transaction;


// I NEED MORE EXAMPLE SMS'S FROM YU TO COMPLETE THIS!!!
class Parser {
	const PAYMENT_RECEIVED = 300;
	const PAYMENT_SENT = 301;
	const DEPOSIT = 302;
	const UNKNOWN = 310;

	public function timeInput($time) {
		$dt = \DateTime::createFromFormat("d-m-Y H:i:s", $time);
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

		if (strpos($input, "yuCash payment sent ") !== FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_OUT;
			$result["TYPE"] = Parser::PAYMENT_SENT;
		
			$temp = array();
			preg_match_all("/KES\s+([0-9\.\,]+)\s+yuCash payment sent to\s+(.+)\s+-\s+([0-9]+)\.\s+Fees:\s+KES\s+([0-9\.\,]+)\.\s+Balance:\s+KES\s+([0-9\.\,]+)\.\s+TxnId:\s+([0-9]+)/mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[6][0];
				$result["AMOUNT"] = Utility::numberInput($temp[1][0]);
				$result["NAME"] = $temp[2][0];
				$result["ACCOUNT"] = $temp[3][0];
				$result["TIME"] = time();
				$result["BALANCE"] = Utility::numberInput($temp[5][0]);
				$result["COSTS"] = Utility::numberInput($temp[4][0]);
			}

		} elseif (strpos($input, "Successful Deposit: ") !== FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_IN;
			$result["TYPE"] = Parser::DEPOSIT;
		
			$temp = array();
			preg_match_all("/Successful Deposit: StoreName\s+(.+)\s+AgentID\s+([0-9]+)\s+Amt\s+KES\s+([0-9\.\,]+)\s+Balance\s+
KES\s+([0-9\.\,]+)\s+Date\s+(.+)\s+Txn\s+ ID\s+([0-9]+)/mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[6][0];
				$result["AMOUNT"] = Utility::numberInput($temp[3][0]);
				$result["NAME"] = $temp[1][0];
				$result["ACCOUNT"] = $temp[2][0];
				$result["TIME"] = $this->timeInput($temp[5][0]);
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