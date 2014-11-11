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

class Transaction extends \PLUSPEOPLE\PesaPi\Base\Transaction {
	// Extended attributes
	const KE_AIRTEL_PAYBILL_PAYMENT_RECEIVED = 901;

	const KE_AIRTEL_PAYBILL_UNKOWN = 999;


	public static function updateData($row, $account) {
		$existing = $account->locateByReceipt($row['RECEIPT'], false);

		if (count($existing) > 0 AND is_object($existing[0])) {
			if ($existing[0]->getSuperType() != $row['SUPER_TYPE']) {
				// NOT DONE - MAJOR ISSUE - ALERT OPERATOR..
			}
			if ($existing[0]->getType() != $row['TYPE']) {
				// NOT DONE - MAJOR ISSUE - ALERT OPERATOR..
			}

			// Merge the information assuming we can piece together a full picture by two half successfull notifications			
			if ($existing[0]->getTime() == 0 AND $row['TIME'] > 0) {
				$existing[0]->setTime($row["TIME"]);
			}
			if (trim($existing[0]->getPhonenumber()) == "" AND !empty($row['PHONE'])) {
				$existing[0]->setPhonenumber($row['PHONE']);
			}
			if (trim($existing[0]->getName()) == "" AND !empty($row['NAME'])) {
				$existing[0]->setName($row['NAME']);
			}
			if (trim($existing[0]->getAccount()) == "" AND !empty($row['ACCOUNT'])) {
				$existing[0]->setAccount($row['ACCOUNT']);
			}
			if ($row['STATUS'] == "" AND $existing[0]->getStatus() != $row['STATUS']) {
				$existing[0]->setStatus($row['STATUS']);
			}
			if ($existing[0]->getAmount() < $row['AMOUNT']) {
				$existing[0]->setAmount($row['AMOUNT']);
			}
			if ($existing[0]->getPostBalance() < $row['BALANCE']) {
				$existing[0]->setPostBalance($row['BALANCE']);
			}
			if (trim($existing[0]->getNote()) == "" AND !empty($row['NOTE'])) {
				$existing[0]->setNote($row['NOTE']);
			}
			
			$existing[0]->update();
			return array($existing[0], false);

		} else {
			return array(Transaction::import($account, $row), true);
		}
	}

	public static function import($account, $row) {
		$payment = Transaction::createNew($account->getId(), $row['SUPER_TYPE'], $row['TYPE']);
		if (is_object($payment)) {
			$payment->setReceipt($row['RECEIPT']);
			$payment->setTime($row["TIME"]);
			$payment->setPhonenumber($row['PHONE']);
			$payment->setName($row['NAME']);
			$payment->setAccount($row['ACCOUNT']);
			$payment->setStatus($row['STATUS']);
			$payment->setAmount($row['AMOUNT']);
			$payment->setPostBalance($row['BALANCE']);
			$payment->setNote($row['NOTE']);

			$payment->update();
			return $payment;
		}
		return null;
	}

}

?>