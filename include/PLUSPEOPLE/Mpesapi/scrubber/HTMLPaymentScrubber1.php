<?php
namespace PLUSPEOPLE\Mpesapi\scrubber;
use \PLUSPEOPLE\Mpesapi\Payment;

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
			$temp['TYPE'] = Payment::TYPE_PAYMENT_RECIEVED;
		} elseif (preg_match('/<td.*>\s*Cancelled:Payment received from\s*/', $rawtext) > 0) {
			// Payment cancellation
			$temp['TYPE'] = Payment::TYPE_PAYMENT_CANCELLATION;
		} elseif (preg_match('/<td.*>\s*Funds Transfer to\s*/', $rawtext) > 0) {
			// Funds transfer
			$temp['TYPE'] = Payment::TYPE_FUNDS_TRANSFER;
		} elseif (preg_match('/<td.*>\s*Cancelled:Funds Transfer to\s*/', $rawtext) > 0) {
			// Funds cancellation
			$temp['TYPE'] = Payment::TYPE_FUNDS_CANCELLATION;
		} elseif (preg_match('/<td.*>\s*Settle Business Charges from\s*/', $rawtext) > 0) {
			// Business charges settlement
			$temp['TYPE'] = Payment::TYPE_BUSINESS_CHARGES;
		} elseif (preg_match('/<td.*>\s*Cancelled:Settle Business Charges from\s*/')) {
			// Business charges settlement cancellation
			$temp['TYPE'] = Payment::TYPE_BUSINESS_CHARGES_CANCELLATION;
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
		case Payment::TYPE_PAYMENT_RECIEVED:
		case Payment::TYPE_PAYMENT_CANCELLATION:
			/*
			$matches = array();
			if (preg_match() > 0) {

			}
			*/
			break;

		case Payment::TYPE_FUNDS_TRANSFER:
		case Payment::TYPE_FUNDS_CANCELLATION:
		case Payment::TYPE_BUSINESS_CHARGES:
		case Payment::TYPE_BUSINESS_CHARGES_CANCELLATION:
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