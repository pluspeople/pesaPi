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
		Based on examples provided by Ali Saiid
 */
namespace PLUSPEOPLE\PesaPi\SomaliaGolisPrivate;

class Parser extends \PLUSPEOPLE\PesaPi\Base\Parser{
	const DATE_FORMAT = "n/j/Y h:i:s A";

	// Custom numberInput function needed as Somalia uses 3 decimal digits.
	public function numberInput($input) {
		$input = trim($input);
		$amount = 0;

		if (preg_match("/^[0-9,]+\.?$/", $input)) {
			$amount = 1000 * (int)str_replace(',', '', $input);
		} elseif (preg_match("/^[0-9,]+\.[0-9]$/", $input)) {
			$amount = 100 * (int)str_replace(array('.', ','), '', $input);
		} elseif (preg_match("/^[0-9,]*\.[0-9][0-9]$/", $input)) {
			$amount = 10 * (int)str_replace(array('.', ','), '', $input);
		} elseif (preg_match("/^[0-9,]*\.[0-9][0-9][0-9]$/", $input)) {
			$amount = (int)str_replace(array('.', ','), '', $input);
		} else {
			$amount = (int)$input;
		}
		return $amount;
	}

	public function parse($input) {
		$result = $this->getBlankStructure();

		// REFACTOR: should be split into subclasses
		// [SAHAL] Ref:302228123 confirmed. $100 Received from c/risaaq axmed(7763277) on 10/23/2014 12:07:57 PM. New A/c balance is $101.780.
		if (strpos($input, " Received from ") !== FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_IN;
			$result["TYPE"] = Transaction::SO_GOLIS_PRIVATE_PAYMENT_RECEIVED;

			$temp = array();
			preg_match_all("/.*Ref:(\d+) confirmed\. \\$([0-9\.\,]+) Received from ([^\(]+)\((\d+)\) on (\d\d?\/\d\d?\/\d{4}) (\d\d?:\d\d:\d\d [AP]M)\. New A\/c balance is \\$([0-9\.\,]+)\./mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[1][0];
				$result["AMOUNT"] = $this->numberInput($temp[2][0]);
				$result["NAME"] = $temp[3][0];
				$result["PHONE"] = $temp[4][0];
				$result["TIME"] = $this->dateInput(Parser::DATE_FORMAT, $temp[5][0] . " " . $temp[6][0]);
				$result["BALANCE"] = $this->numberInput($temp[7][0]);
			}

		// [SAHAL] Tix:307013277 waxaad $1 ka heshay CABDILAAHI MIRRE AXMED MUUSE(252633659717) tar:11/6/2014 10:32:40 AM. Haraagaagu waa $55.980.
		} elseif (strpos($input, " ka heshay ") !== FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_IN;
			$result["TYPE"] = Transaction::SO_GOLIS_PRIVATE_PAYMENT_RECEIVED;

			$temp = array();
			preg_match_all("/.*Tix:(\d+) waxaad \\$([0-9\.\,]+) ka heshay ([^\(]+)\((\d+)\) tar:(\d\d?\/\d\d?\/\d{4}) (\d\d?:\d\d:\d\d [AP]M)\. Haraagaagu waa \\$([0-9\.\,]+)\./mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = $temp[1][0];
				$result["AMOUNT"] = $this->numberInput($temp[2][0]);
				$result["NAME"] = $temp[3][0];
				$result["PHONE"] = $temp[4][0];
				$result["TIME"] = $this->dateInput(Parser::DATE_FORMAT, $temp[5][0] . " " . $temp[6][0]);
				$result["BALANCE"] = $this->numberInput($temp[7][0]);
			}


		} else {
			$result["SUPER_TYPE"] = Transaction::MONEY_NEUTRAL;
			$result["TYPE"] = Transaction::SO_GOLIS_PRIVATE_UNKOWN;
		}

		return $result;
	}

}

?>