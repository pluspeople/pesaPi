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
 */
namespace PLUSPEOPLE\Pesapi\simulator;

class Database {
	############## Properties ####################
	const TYPE_READ = "Read";
	const TYPE_WRITE = "Write";

	protected $config;
	protected $queryAmount = 0;
	protected $dbId = 0;
	protected $transactionCount = 0;

	############## Methods #######################
	# # # # # # # # Initializer # # # # # # # # # #
	protected function __construct($type) {
		$this->config = Configuration::instantiate();
		// if we need to use same credentials to the database then we need to use mysql_connect instead of mysql_pconnect.
		$this->dbId = mysql_pconnect($this->config->getConfig("DatabaseHost" . $type),$this->config->getConfig("DatabaseUser" . $type),$this->config->getConfig("DatabasePassword" . $type), true);
		if ($this->dbId > 0) {
			if (!mysql_select_db($this->config->getConfig("DatabaseDatabase" . $type), $this->dbId)) {
				exit;
			}
		}
	}

	// use this to check how many querys are posted.
	public function getQueryAmount() {
		return $this->queryAmount;		
	}

	public static function instantiate($type = Database::TYPE_READ) {
		global $singletonArray;

		if (!isset($singletonArray["Database" . $type] )) {
			$singletonArray["Database" . $type] = new Database($type);
		}
		return $singletonArray["Database" . $type];
	}

	public function dbIn($input) {
		return addslashes($input);
	}

	public function dbOut($input) {
		return stripslashes($input);
	}

	public function query($input) {
		++$this->queryAmount;
		return mysql_query($input, $this->dbId);		
	}

	public function fetchObject($input) {
		return mysql_fetch_object($input);
	}

	public function freeResult($input) {
		return mysql_free_result($input);
	}

	public function insertId() {
    return mysql_insert_id($this->dbId);
	} 

	public function affectedRows() {
		return mysql_affected_rows($this->dbId);
	}

	public function numRows($input) {
		return mysql_num_rows($input);
	}

	public function beginTransaction() {
		$this->transactionCount++;
		if ($this->transactionCount == 1) {
			return (bool)mysql_query("START TRANSACTION");
		}
		return true;
	}

	public function commitTransaction() {
		if ($this->transactionCount > 0) {
			$this->transactionCount--;
		}
		if ($this->transactionCount == 0) {
			return (bool)mysql_query("COMMIT");
		}
		return true;
	}

	public function rollbackTransaction() {
		if ($this->transactionCount != 0) {
			$this->transactionCount = 0;
			return (bool)mysql_query("ROLLBACK");
		}
		return true;
	}
}
?>