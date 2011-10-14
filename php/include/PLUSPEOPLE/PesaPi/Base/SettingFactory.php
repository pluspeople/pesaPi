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
namespace PLUSPEOPLE\PesaPi\Base;

class SettingFactory {
  ############### Properties ####################
  const SELECTLIST = "
SELECT id,
type,
name,
value_string,
UNIX_TIMESTAMP(value_date) AS value_date,
value_int ";

  # # # # # # # # misc methods # # # # # # # #
  static public function factoryOne($id) {
    $db = Database::instantiate(Database::TYPE_READ);
    $id = (int)$id;

	  $query = SettingFactory::SELECTLIST . "
							FROM  pesapi_setting
							WHERE	id = '$id' ";
		
		if ($result = $db->query($query) AND $foo = $db->fetchObject($result)) {
		  $returnval = new Setting($foo->id, $foo);
		  $db->freeResult($result);
		  return $returnval;
		}
  }

  static public function factoryByName($name) {
    $db = Database::instantiate(Database::TYPE_READ);
    $name = $name;

	  $query = SettingFactory::SELECTLIST . "
							FROM  pesapi_setting
							WHERE	name = '" . $db->dbIn($name) . "' ";

		if ($result = $db->query($query) AND $foo = $db->fetchObject($result)) {
		  $returnval = new Setting($foo->id, $foo);
		  $db->freeResult($result);
		  return $returnval;
		}
  }

  static public function factoryAll() {
    $db = Database::instantiate(Database::TYPE_READ);
		$tempArray = array();

	  $query = SettingFactory::SELECTLIST . "
							FROM  pesapi_setting ";
		
		if ($result = $db->query($query)) {
			while($foo = $db->fetchObject($result)) {
				$tempArray[] = new Setting($foo->id, $foo);
			}
			$db->freeResult($result);
		}
		return $tempArray;
  }
}
?>