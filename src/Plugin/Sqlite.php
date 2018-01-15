<?php

namespace Hunter\backup\Plugin;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Back up couchdb database or databases on local or remote via ssh
 */
class Sqlite {

	//path to database
	private $databasePath = "";

	//backup file name
	private $backupFilename = "";

	//backup result
	private $result = array(
		'status' => null,
		'message' => "",
	);

	/**
	 * Prepare stuff for some cooking :)
	 */
	function __construct($filename, $database_path) {
		$this->databasePath = $database_path;
		$this->backupFilename = $filename . ".sql";
	}

	/**
	 * Dump some data
	 */
	public function dump() {
		$localDumpResult = $this->dumpLocal();

		if (!$localDumpResult) {
			return $this->result;
		}

		//if everything is ok, then prepare result for main backup class
		$this->result['status'] = 1;
		$this->result['message'] = "Successful backup of SQLite in local file: " . $this->backupFilename;
		$this->result['backup_filename'] = $this->backupFilename;
		$this->result['full_path'] = $this->backupFilename;

		return $this->result;
	}

	/**
	 * Local datababe(s) backup
	 */
	private function dumpLocal() {

		if ($this->databasePath == '') {
			$this->result['status'] = 0;
			$this->result['message'] = "You have to define database path!";

			return false;
		}

		//check if database exists, in other case return error
		if (!file_exists($this->databasePath)) {
			$this->result['status'] = 0;
			$this->result['message'] = "Defined Couchdb does not exist!";

			return false;
		}

    if(!is_dir(dirname($this->backupFilename))){
      mkdir(dirname($this->backupFilename), 0755, true);
    }

		//make a dump of our database
		$dumpDbCommand = "sqlite3 " . $this->databasePath . " .dump > " . $this->backupFilename;
		$dumpDb = new Process($dumpDbCommand);

		$dumpDb->run();

		//something bad happend, return error
		if (!$dumpDb->isSuccessful()) {
			throw new ProcessFailedException($dumpDb);
		}

		return true;
	}

}
