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
 */
namespace PLUSPEOPLE\PesaPi\KenyaAirtelPaybill;

class Parser {
	public function scrubTransactions($rawtext) {
		$result = array();
		$temp = array();

		preg_match_all('/Source&nbsp;Info<\/td>[\s\n\r]*<\/tr>(.+)<tr style="vertical-align:top;">[\s\n\r]*<td style="width:0px;height:387px;"><\/td>/msi', $rawtext, $temp);
		if (isset($temp[1][0])) {
			$rows = array_reverse(preg_split('/<\/tr>[\s\n\r]*<tr style="vertical-align:top;">/', $temp[1][0]));
			foreach($rows AS $row) {
				$transaction = $this->scrubRow($row);
				if ($transaction != null) {
					$result[] = $transaction;
				}
			}
		}

		return $result;
	} 

	public function scrubRow($rawtext) {
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

		$temp = array();
		preg_match_all('/<td [^>]*>(.*)<\/td>/Umsi', $rawtext, $temp);

		if (isset($temp[1]) AND count($temp[1]) >= 11) {
			if ($temp[1][9] == "&nbsp;-&nbsp;CASH&nbsp;RECEIVE") {
				$result["SUPER_TYPE"] = Transaction::MONEY_IN;
				$result["TYPE"] = Transaction::KE_AIRTEL_PAYBILL_PAYMENT_RECEIVED;
				$result["RECEIPT"] = trim(strip_tags($temp[1][3]));
				$result["TIME"] = strtotime(str_replace('&nbsp;', ' ', $temp[1][2]));
				$result["PHONE"] = trim(strip_tags($temp[1][4]));
				$result["NAME"] = trim(str_replace('&nbsp;', ' ', $temp[1][10]));
				//				$result["ACCOUNT"] = "NOT DONE"; // NOT DONE
				$result["STATUS"] = Transaction::STATUS_COMPLETED;
				$result["AMOUNT"] = (int)(((double)$temp[1][8])*100);
				$result["BALANCE"] = (int)(((double)$temp[1][7])*100);

			} else {
				$result["SUPER_TYPE"] = Transaction::MONEY_NEUTRAL;
				$result["TYPE"] = Transaction::KE_AIRTEL_PAYBILL_UNKOWN;
				$result["NOTE"] = $rawtext;
			}

			return $result;
		}
		return null;
	}

}

?>