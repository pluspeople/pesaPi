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
namespace PLUSPEOPLE\PesaPi\MpesaPaybill;

class HTMLPaymentScrubber1 {
	const VERSION = "1.0";

	static public function scrubUrls(&$rawtext) {
		$temp = array();

		return $temp;
	}

	static public function scrubPaymentRows(&$rawtext) {
		$temp = array();

		preg_match_all('/<tr class="Grid(Alt)?Row_Default">.+<\/tr>/Umsi', $rawtext, $temp);

		return $temp[0];
	} 

	static public function scrubPayment(&$rawtext) {
		$temp = array("VALID" => false,
									"TYPE" => null,
									"TRANSFERDIRECTION" => null,
									"RECIEPT" => null,
									"TIME" => null,
									"PHONENUMBER" => null,
									"NAME" => null,
									"ACCOUNT" => null,
									"STATUS" => null,
									"AMOUNT" => null,
									"POST_BALANCE" => null,
									"NOTE" => null);

		/////////////////////////
		// First identify those properties that are the same for all types
		// Reciept
		$matches = array();
		if (preg_match('/<td\s*>\s*<a\s.+>(.+)<\/a\s*>\s*<\/td\s*>/iU', $rawtext, $matches) > 0) {
			$temp['RECIEPT'] = trim($matches[1]);
		}
		
		// Time
		$matches = array();
		if (preg_match('/<td.*>\s*(2[0-9]{3,3}-[01][0-9]-[0-3][0-9]\s[0-2][0-9]:[0-6][0-9]:[0-6][0-9])\s*<\/td\s*>/iU', $rawtext, $matches) > 0) {
			$temp['TIME'] = Scrubber::dateInput($matches[1]);
		}

		// Amount
		$matches = array();
		// Maches the row that has a number in it.
		if (preg_match('/<td.*>\s*([0-9]+\.[0-9][0-9])\s*<\/td\s*>/iU', $rawtext, $matches) > 0) {
			$temp['AMOUNT'] = Scrubber::numberInput($matches[1]);
		}


		////////////////////////////
		// Next figure out what type the entry is - and then perform specific identification 
		if (preg_match('/<td.*>\s*Payment received from\s*/', $rawtext) > 0) {
			// Payment recieved
			$temp['TYPE'] = Transaction::MPESA_PAYBILL_PAYMENT_RECIEVED;
		} elseif (preg_match('/<td.*>\s*Cancelled:Payment received from\s*/', $rawtext) > 0) {
			// Payment cancellation
			$temp['TYPE'] = Transaction::MPESA_PAYBILL_PAYMENT_CANCELLATION;
		} elseif (preg_match('/<td.*>\s*Funds Transfer to\s*/', $rawtext) > 0) {
			// Funds transfer
			$temp['TYPE'] = Transaction::MPESA_PAYBILL_FUNDS_TRANSFER;
		} elseif (preg_match('/<td.*>\s*Cancelled:Funds Transfer to\s*/', $rawtext) > 0) {
			// Funds cancellation
			$temp['TYPE'] = Transaction::MPESA_PAYBILL_FUNDS_CANCELLATION;
		} elseif (preg_match('/<td.*>\s*Settle Business Charges from\s*/', $rawtext) > 0) {
			// Business charges settlement
			$temp['TYPE'] = Transaction::MPESA_PAYBILL_BUSINESS_CHARGES;
		} elseif (preg_match('/<td.*>\s*Cancelled:Settle Business Charges from\s*/', $rawtext) > 0) {
			// Business charges settlement cancellation
			$temp['TYPE'] = Transaction::MPESA_PAYBILL_BUSINESS_CHARGES_CANCELLATION;
		} else {
			// Unkown type - report it back to Mpesapi
			// TODO: feedback mechanism
		}

		// Transaction details
		$matches = array();
		if (preg_match('/<td.*>\s*Payment received from\s*([0-9]+)\s*-\s+(.*)\s+Acc\.\s*(.*)\s*<\/td\s*>/iU', $rawtext, $matches) > 0) {
			$temp['PHONENUMBER'] = trim($matches[1]);
			$temp['NAME'] = trim($matches[2]);
			$temp['ACCOUNT'] = trim($matches[3]);
		}

		// Status & note
		switch($temp['TYPE']) {
		case Transaction::MPESA_PAYBILL_PAYMENT_RECIEVED:
		case Transaction::MPESA_PAYBILL_PAYMENT_CANCELLATION:
			/*
			$matches = array();
			if (preg_match() > 0) {

			}
			*/
			break;

		case Transaction::MPESA_PAYBILL_FUNDS_TRANSFER:
		case Transaction::MPESA_PAYBILL_FUNDS_CANCELLATION:
		case Transaction::MPESA_PAYBILL_BUSINESS_CHARGES:
		case Transaction::MPESA_PAYBILL_BUSINESS_CHARGES_CANCELLATION:
			break;
		default:
			// Unkown status - report back to Mpesapi
		}
				$temp['STATUS'] = Payment::STATUS_COMPLETED;


		// Post Balance
		$matches = array();
		if (preg_match('/<td.*>\s*<span.*Balance">(.+)<\/span\s*>\s*<\/td\s*>/iU', $rawtext, $matches) > 0) {
			$temp['POST_BALANCE'] = Scrubber::numberInput($matches[1]);
		}

								 
		return $temp;
	}

}

?>