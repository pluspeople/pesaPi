<?php
/*	Copyright (c) 2011-2015, PLUSPEOPLE Kenya Limited. 
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
namespace PLUSPEOPLE\PesaPi\Base;

class AccountFactory {
  ############### Properties ####################
  const SELECTLIST = "
SELECT id,
type,
name,
identifier,
push_in,
push_out,
push_neutral,
settings ";

  //# # # # # # # # misc methods # # # # # # # #
  static public function factoryOne($id) {
    $db = Database::instantiate(Database::TYPE_READ);
    $id = (int)$id;

	  $query = AccountFactory::SELECTLIST . "
							FROM  pesapi_account
							WHERE	id = '$id' ";
		
		if ($result = $db->query($query) AND $foo = $db->fetchObject($result)) {
		  $returnval = AccountFactory::createEntry($foo->type, $foo->id, $foo);
		  $db->freeResult($result);
		  return $returnval;
		}
  }

  static public function factoryByIdentifier($id) {
    $db = Database::instantiate(Database::TYPE_READ);
    $id = $id;

		if ($id != "") {
			$query = AccountFactory::SELECTLIST . "
							FROM  pesapi_account
							WHERE identifier = '" . $db->dbIn("$id") . "' ";
			
			if ($result = $db->query($query) AND $foo = $db->fetchObject($result)) {
				$returnval = AccountFactory::createEntry($foo->type, $foo->id, $foo);
				$db->freeResult($result);
				return $returnval;
			}
		}
		return null;
  }

  static function factoryAll() {
    $db = Database::instantiate(Database::TYPE_READ);

	  $query = AccountFactory::SELECTLIST . "
							FROM  pesapi_account ";

		$tempArray = array();
		if ($result = $db->query($query)) {
			while($foo = $db->fetchObject($result)) {
				$tempArray[] = AccountFactory::createEntry($foo->type, $foo->id, $foo);
			}
			$db->freeResult($result);
		}
		return $tempArray;
  }


  public static function createEntry($type, $id, $initValues=NULL) {
    switch($type) {
    case Account::MPESA_PAYBILL:
      $object = new \PLUSPEOPLE\PesaPi\MpesaPaybill\MpesaPaybill($id, $initValues);
    break;
    case Account::MPESA_PRIVATE:
      $object = new \PLUSPEOPLE\PesaPi\MpesaPrivate\MpesaPrivate($id, $initValues);
    break;
    case Account::KENYA_YU_PRIVATE:
      $object = new \PLUSPEOPLE\PesaPi\KenyaYuPrivate\Account($id, $initValues);
    break;
    case Account::GHANA_AIRTEL_PRIVATE:
      $object = new \PLUSPEOPLE\PesaPi\GhanaAirtelPrivate\Account($id, $initValues);
    break;
    case Account::RWANDA_MTN_PRIVATE:
      $object = new \PLUSPEOPLE\PesaPi\RwandaMTNPrivate\Account($id, $initValues);
    break;
    case Account::TANZANIA_MPESA_PRIVATE:
      $object = new \PLUSPEOPLE\PesaPi\TanzaniaMpesaPrivate\Account($id, $initValues);
    break;
    case Account::TANZANIA_TIGO_PRIVATE:
      $object = new \PLUSPEOPLE\PesaPi\TanzaniaTigoPrivate\Account($id, $initValues);
    break;
		case Account::KENYA_AIRTEL_PRIVATE:
			$object = new \PLUSPEOPLE\PesaPi\KenyaAirtelPrivate\Account($id, $initValues);
		break;
		case Account::KENYA_AIRTEL_PAYBILL:
			$object = new \PLUSPEOPLE\PesaPi\KenyaAirtelPaybill\Account($id, $initValues);
		break;
		case Account::SOMALIA_GOLIS_PRIVATE:
			$object = new \PLUSPEOPLE\PesaPi\SomaliaGolisPrivate\Account($id, $initValues);
		break;
		case Account::SOMALIA_TELESOME_PRIVATE:
			$object = new \PLUSPEOPLE\PesaPi\SomaliaTelesomePrivate\Account($id, $initValues);
		break;
		case Account::SOMALIA_HORMUUD_PRIVATE:
			$object = new \PLUSPEOPLE\PesaPi\SomaliaHormuudPrivate\Account($id, $initValues);
		break;
		case Account::GHANA_MTN_PRIVATE:
			$object = new \PLUSPEOPLE\PesaPi\GhanaMTNPrivate\Account($id, $initValues);
		break;
		case Account::DR_CONGO_MPESA_PRIVATE:
			$object = new \PLUSPEOPLE\PesaPi\CongoMpesaPrivate\Account($id, $initValues);
		break;
		case Account::UGANDA_MTN_PRIVATE:
			$object = new \PLUSPEOPLE\PesaPi\UgandaMTNPrivate\Account($id, $initValues);
		break;
    }
    return $object;
  }

}
?>