<?php
/*
User form for initiating a bulk ingest.  User must have already uploaded ingestion folders to a server-accessible folder.
Author: Terry Brady, Georgetown University Libraries

License information is contained below.

Copyright (c) 2014, Georgetown University Libraries All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer. 
in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials 
provided with the distribution. THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, 
BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

See http://techdocs.iii.com/sierradna/

*/

class Sierra {
	private $user = "SIERRA_USER";
	private $pass = "SIERRA_PASS";
	private $host = "SIERRA_HOST";
	private $port = 1032;
	private $db   = "iii";
	
	public function getPdoDb() {
        return new PDO("pgsql:host={$this->host} port={$this->port} dbname={$this->db} user={$this->user} password={$this->pass} sslmode=require");
 	}
	
	public $USERS = array();
	
	function __construct() {
		$sql = "select u.name, u.full_name from sierra_view.iii_user u order by full_name";
	    $dbh = $this->getPdoDb();
	    $stmt = $dbh->prepare($sql);
	    $arg = array();
	    $result = $stmt->execute($arg);
 	    if (!$result) {
 		    print($sql);
  	        print_r($dbh->errorInfo());
    	    die("Error in SQL query: ");
 	    }       
	    $result = $stmt->fetchAll();
 	    $ret = "";
 	    foreach ($result as $row) {
 	    	$this->USERS[$row[0]] = $row[1];
	    }  

	}

	public function getUserWidget($name, $desc, $curval) {
		$users = "<option/>";
		foreach($this->USERS as $id=>$uname) {
			$sel = ($curval == $id) ? "selected" : "";
			$users .= "<option {$sel} value='{$id}'>{$uname} - {$id}</option>";
		}
		echo <<< HERE
		<div class="userWidget">
		<label for="{$name}">{$desc}</label>
		<select id="{$name}" name="{$name}">
		  {$users}
		</select>
		</div>
HERE;
	}

}	
	
