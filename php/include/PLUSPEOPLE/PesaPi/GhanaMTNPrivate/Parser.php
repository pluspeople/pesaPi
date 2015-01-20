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
		Based on examples provided by Baba Musah
 */
namespace PLUSPEOPLE\PesaPi\GhanaMTNPrivate;

class Parser extends \PLUSPEOPLE\PesaPi\Base\Parser {
	public function parse($input) {
		$result = $this->getBlankStructure();

		// REFACTOR: should be split into subclasses
		if (strpos($input, "Payment received for GHC") !== FALSE) {
			$result["SUPER_TYPE"] = Transaction::MONEY_IN;
			$result["TYPE"] = Transaction::GH_MTN_PRIVATE_PAYMENT_RECEIVED;

			$temp = array();
			preg_match_all("/MobileMoney Advice[\s\n\r]+Payment received for GHC([0-9\.\,]+) from ([0-9A-Za-z '\.]+)[\s\n\r]+Current Balance: GHC([0-9\.\,]+)[\s\n\r]+Available Balance: GHC([0-9\.\,]+)[\s\n\r]+Reference: (.*)[\s\n\r]+QJV/mi", $input, $temp);
			if (isset($temp[1][0])) {
				$result["RECEIPT"] = time(); // MTN does not publish reference numbers - stupid.
				$result["AMOUNT"] = $this->numberInput($temp[1][0]);
				$result["NAME"] = $temp[2][0];
				$result["TIME"] = time();
				$result["BALANCE"] = $this->numberInput($temp[3][0]);
				$result["ACCOUNT"] = trim($temp[5][0]);
			}

		} else {
			$result["SUPER_TYPE"] = Transaction::MONEY_NEUTRAL;
			$result["TYPE"] = Transaction::GH_MTN_PRIVATE_UNKOWN;
		}

		return $result;
	}

}

?>