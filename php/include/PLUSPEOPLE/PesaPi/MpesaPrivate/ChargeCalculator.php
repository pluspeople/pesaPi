<?php
/*	Copyright (c) 2013, PLUSPEOPLE Kenya Limited. 
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
namespace PLUSPEOPLE\PesaPi\MpesaPrivate;

class ChargeCalculator {
	
	static public function calculateCost($type, $time, $amount) {
		switch ($type) {
		case Transaction::MPESA_PRIVATE_PAYMENT_SENT:
			return ChargeCalculator::sendingCost($time, $amount);
			break;
		case Transaction::MPESA_PRIVATE_WITHDRAW:
			return ChargeCalculator::withdrawCost($time, $amount);
			break;

		case Transaction::MPESA_PRIVATE_WITHDRAW_ATM:
			return ChargeCalculator::atmWithdrawCost($time, $amount);
			break;
		case Transaction::MPESA_PRIVATE_BALANCE_REQUEST:
			return 100;
			break;
		}

		return 0;
	}

	static protected function sendingCost($time, $amount) {
		if ($time > 140951880) {
			// Rates 1.Sep 2014 -
			if ($amount <= 4900) {
				return 100;
			} elseif ($amount <= 10000) {
				return 300;
			} elseif ($amount <= 50000) {
				return 1100;
			} elseif ($amount <= 100000) {
				return 1500;
			} elseif ($amount <= 150000) {
				return 2500;
			} elseif ($amount <= 250000) {
				return 4000;
			} elseif ($amount <= 350000) {
				return 5500;
			} elseif ($amount <= 500000) {
				return 6000;
			} elseif ($amount <= 750000) {
				return 7500;
			} elseif ($amount <= 1000000) {
				return 8500;
			} elseif ($amount <= 1500000) {
				return 9500;
			} elseif ($amount <= 2000000) {
				return 10000;
			} else {
				return 11000;
			}

		} else {
			// Rates: 8.Feb 2013 to 1.Sep 2014
			if ($amount <= 4900) {
				return 300;
			} elseif ($amount <= 10000) {
				return 500;
			} elseif ($amount <= 50000) {
				return 2700;
			} elseif ($amount <= 500000) {
				return 3300;
			} elseif ($amount <= 2000000) {
				return 5500;
			} elseif ($amount <= 4500000) {
				return 8200;
			} else {
				return 11000;
			}
		}
	}

	static protected function withdrawCost($time, $amount) {
		if ($time > 140951880) {
			// Rates 1.Sep 2014 -
			if ($amount <= 10000) {
				return 100;
			} elseif ($amount <= 250000) {
				return 2700;
			} elseif ($amount <= 350000) {
				return 4900;
			} elseif ($amount <= 500000) {
				return 6600;
			} elseif ($amount <= 750000) {
				return 8200;
			} elseif ($amount <= 1000000) {
				return 11000;
			} elseif ($amount <= 1500000) {
				return 15900;
			} elseif ($amount <= 2000000) {
				return 17600;
			} elseif ($amount <= 3500000) {
				return 18700;
			} elseif ($amount <= 5000000) {
				return 27500;
			} else {
				return 33000;
			}

		} else {
			// Rates: 8.Feb 2013 to 1.Sep 2014
			if ($amount <= 10000) {
				return 1000;
			} elseif ($amount <= 250000) {
				return 2700;
			} elseif ($amount <= 350000) {
				return 4900;
			} elseif ($amount <= 500000) {
				return 6600;
			} elseif ($amount <= 750000) {
				return 8200;
			} elseif ($amount <= 1000000) {
				return 11000;
			} elseif ($amount <= 1500000) {
				return 15900;
			} elseif ($amount <= 2000000) {
				return 17600;
			} elseif ($amount <= 3500000) {
				return 18700;
			} elseif ($amount <= 5000000) {
				return 27500;
			} else {
				return 33000;
			}
		}
	}

	static protected function atmWithdrawCost($time, $amount) {
		if ($amount <= 250000) {
			return 3300;
		} elseif ($amount <= 500000) {
			return 6600;
		} elseif ($amount <= 1000000) {
			return 11000;
		} else {
			return 19300;
		}
	}

}