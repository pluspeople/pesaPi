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
namespace PLUSPEOPLE\PesaPi\TanzaniaMpesaPrivate;

class ChargeCalculator {
	
	static public function calculateCost($type, $time, $amount) {
		switch ($type) {
		case Transaction::TZ_MPESA_PRIVATE_PAYMENT_SENT:
			return ChargeCalculator::sendingCost($time, $amount);
			break;
		case Transaction::TZ_MPESA_PRIVATE_WITHDRAW:
			return ChargeCalculator::withdrawCost($time, $amount);
			break;
		case Transaction::TZ_MPESA_PRIVATE_BALANCE_REQUEST:
			return 6000;
			break;
		}
		return 0;
	}

	static protected function sendingCost($time, $amount) {
		if ($amount <= 99900) {
			return 1000;
		} elseif ($amount <= 299900) {
			return 3000;
		} elseif ($amount <= 499900) {
			return 6000;
		} elseif ($amount <= 999900) {
			return 10000;
		} elseif ($amount <= 1999900) {
			return 25000;
		} elseif ($amount <= 4999900) {
			return 30000;
		} elseif ($amount <= 29999900) {
			return 60000;
		} elseif ($amount <= 49999900) {
			return 120000;
		} else {
			return 180000;
		}
	}

	static protected function withdrawCost($time, $amount) {
		if ($amount <= 299900) {
			return 50000;
		} elseif ($amount <= 999900) {
			return 60000;
		} elseif ($amount <= 1999900) {
			return 120000;
		} elseif ($amount <= 4999900) {
			return 150000;
		} elseif ($amount <= 9999900) {
			return 220000;
		} elseif ($amount <= 19999900) {
			return 260000;
		} elseif ($amount <= 29999900) {
			return 420000;
		} elseif ($amount <= 39999900) {
			return 550000;
		} elseif ($amount <= 49999900) {
			return 650000;
		} else {
			return 700000;
		}
	}

}