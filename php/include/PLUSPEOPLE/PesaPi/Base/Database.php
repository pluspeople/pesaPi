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
		$this->config = \PLUSPEOPLE\PesaPi\Configuration::instantiate();

		$this->db = new \mysqli($this->config->getConfig("DatabaseHost" . $type), 
										 			  $this->config->getConfig("DatabaseUser" . $type),
													  $this->config->getConfig("DatabasePassword" . $type),
													  $this->config->getConfig("DatabaseDatabase" . $type));

		if ($this->db->connect_errno) {
			print "DB connection error";
			exit();
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
		return $this->db->real_escape_string($input);
	}

	public function dbOut($input) {
		return $input;
	}

	public function query($input) {
		++$this->queryAmount;
		return $this->db->query($input);		
	}

	public function fetchObject($input) {
		return $input->fetch_object();
	}

	public function freeResult($input) {
		return $input->close();
	}

	public function insertId() {
		return $this->db->insert_id;
	} 

	public function affectedRows() {
		return $this->db->affected_rows;
	}

	public function numRows($input) {
		return $input->num_rows;
	}

	public function beginTransaction() {
		$this->transactionCount++;
		if ($this->transactionCount == 1) {
			return (bool)$this->query("START TRANSACTION");
		}
		return true;
	}

	public function commitTransaction() {
		if ($this->transactionCount > 0) {
			$this->transactionCount--;
		}
		if ($this->transactionCount == 0) {
			return (bool)$this->query("COMMIT");
		}
		return true;
	}

	public function rollbackTransaction() {
		if ($this->transactionCount != 0) {
			$this->transactionCount = 0;
			return (bool)$this->query("ROLLBACK");
		}
		return true;
	}
}
?>