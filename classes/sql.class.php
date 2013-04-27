<?php

class sql {


	public function __construct() {}
	    
	public function table_read($table, $spalten = '*', $where = '*', $limit = 0, $ordnen = NULL, $asc = false) {
		
		// Parametercheck
		
		if(!is_string($table))
			die('Fehler: $table ist nicht vom Typ string');
		if(!is_array($spalten) and $spalten != '*' and isset($spalten))
			die('Fehler: $spalten ist nicht vom Typ array');
		if(!is_array($where) and $where != '*' and isset($where))
			die('Fehler: $where ist nicht vom Typ array');
		if(!is_int($limit) and !is_string($limit) and isset($limit))
			die('Fehler: $limit ist nicht vom Typ int oder string');
		if(!is_array($ordnen) and isset($ordnen))
			die('Fehler: $ordnen ist nicht vom Typ array');
		
		$asc ? $asc = 'ASC' : $asc = 'DESC';
		
		// Spalten
		
		if(is_array($spalten)) {
			
			$select = implode(',', $spalten);
			
		} else {
			$select = "*";
			}
		
		// From
		
		$from = $table;
		
		// Where
		
		if($where != '*') {
			
			$conditions = array();
			
			foreach($where as $column => $value) {
			  $value = mysql_real_escape_string($value);
				$conditions[] = "$column = '$value'";
			}
			
			$wheresql = "WHERE ". implode(' AND ', $conditions);
			
		} else {
			$wheresql = '';
			}
		
		// Ordnen
		
		if(isset($ordnen)) {
			
			$orderby = "ORDER BY ";
			
			foreach($ordnen as $value) {
				$orderby .= "$value $asc,";
			}
			
			$orderby = substr($orderby, 0, strlen($orderby)-1);
			
		} else {
			$orderby = "";
			}
		
		
		// Limit
		
		if($limit > 0 or is_string($limit))
			$limit = "LIMIT $limit";
		else
			$limit = "";
		
		// Query
		
		$query = "SELECT $select FROM $from $wheresql $orderby $limit;";
		
		$daten = mysql_query($query) or die(mysql_error());
		
		// Return
		
		$tabelle = array();
		
		for($i = 0; $zeile = mysql_fetch_assoc($daten); $i++) {
			
			foreach($zeile as $key => $value) {
				$tabelle[$i][$key] = $value;
			}
			
		}
		
		return $tabelle;
	}
	
	public function table_create($name, $spalten, $spaltentyp) {
	
		// Parametercheck
		
		if(!isset($name) or !is_string($name))
			die('$name ist nicht definiert oder kein String');
		if(!isset($spalten) or !is_array($spalten))
			die('$spalten ist nicht definiert oder kein Array');
		if(!isset($spaltentyp) or !is_array($spaltentyp))
			die('$spaltentyp ist nicht definiert oder kein Array');
		
		// Spaltentyp
		
		$sqltypen = array();
		
		foreach($spaltentyp as $typ) {
			
			$capture = array();
			$typsql;
			
			switch(true) {
			
			case $typ == 'id':
			$typsql = "INT AUTO_INCREMENT PRIMARY KEY";
			break;
			
			case $typ == 'date':
			$typsql = "DATETIME";
			break;
			
			case $typ == 'int':
			$typsql = "INT";
			break;
			
			case $typ == 'smallint':
			$typsql = "SMALLINT";
			break;
			
			case $typ == 'bigint':
			$typsql = "BIGINT";
			break;
			
			case $typ == 'text':
			$typsql = "TEXT";
			break;
			
			case strstr($typ, 'string'):
			preg_match('/^string\((\d+)\)$/', $typ, $capture) or $typ == 'string' or die('$spaltentyp ist ungültig (enthält \'string\')');
			$length = $capture[1];
			empty($length) ? $length = 25 : false;
			$typsql = "VARCHAR($length)";
			break;
			
			case $typ == '':
			$typsql = "INT";
			break;
			
			default:
			$typsql = $typ;
			}
			
			$sqltypen[] = $typsql;
			
		}
		
		// Query erstellen
		
		$query = "CREATE TABLE $name (\n";
		
		foreach($spalten as $key => $spalte) {
			
			$typ = $sqltypen[$key];
			$query .= "$spalte $typ,\n";
			
		}
		
		$query = substr($query, 0, strlen($query)-2);
		
		$query .= ");";
		
		// Query senden
	
		mysql_query($query) or die("Table konnte nicht erstellt werden.\nQuery:\n<pre>$query</pre>".mysql_error());
		
		return true;
	}
	
	public function table_delete($table) {
		
		// Parametercheck
		
		if(!isset($table) or !is_string($table))
			die('$table ist nicht definiert oder kein String');
		
		// Query erstellen
		
		$query = "DROP TABLE $table";
		
		// Query senden
		
		mysql_query($query) or die("Table $table konnte nicht gelöscht werden");
		
		return true;
		
	}
	
	public function line_create($table, $werte, $spalten = array()) {
		
		// Parametercheck
		
		if(!is_string($table) or !isset($table))
			die('$table ist nicht definiert oder kein String');
		
		if(!is_array($werte) or !isset($werte))
			die('$werte ist nicht definiert oder kein Array');
		
		if(!is_array($spalten) or !isset($spalten))
			die('$spalten ist nicht definiert oder kein Array');
		
		// Datum
		
		foreach($werte as $key => $wert) {
			if($wert != 'NOW()') {
			  $wert = mysql_real_escape_string($wert);
				$werte[$key] = "'$wert'";
			}
		}
		
		$spaltensql = implode(',', $spalten);
		
		$wertesql = implode(',', $werte);
		
		// Query erstellen
		
		$query = "INSERT INTO $table (\n";
		$query .= "$spaltensql)\n";
		$query .= "VALUES (\n";
		$query .= "$wertesql);";
		
		// Query senden
		
		mysql_query($query) or die("Datensatz konnte nicht erstellt werden.\nQuery:\n<pre>$query</pre>");
		
		return true;
	}
	
	public function line_edit($table, $where, $werte, $spalten = array()) {
		
		// Parametercheck
		
		if(!isset($table) or !is_string($table))
			die('$table ist nicht definiert oder kein String');
		if(!isset($where) or !is_array($where) or count($where) > 1)
			die('$where ist nicht definiert oder kein Array oder zu großes Array');
		if(!isset($werte) or !is_array($werte))
			die('$werte ist nicht definiert oder kein Array');
		if(!isset($spalten) or !is_array($spalten))
			die('$spalten ist nicht definiert oder kein Array');
		
		// Datum
			
		foreach($werte as $key => $value) {
			if($value != 'NOW()') {
			  $value = mysql_real_escape_string($value);
				$werte[$key] = "'$value'";
			}
		}
		
		// where
		
		foreach($where as $key => $value) {
			$value = mysql_real_escape_string($value);
			$where[$key] = "'$value'";
		}
		
		// ggf. Headlines holen
		
		if(empty($spalten)) {
			
			$query = "DESCRIBE $table";
			$columns = mysql_query($query) or die("Column-Anfrage fehlgeschlagen\nQuery:\n<pre>$query</pre>");
			
			while($zeile = mysql_fetch_assoc($columns)) {
				$spalten[] = $zeile['Field'];
			}
			
		}
		
		// setsql
		
		$setsql = '';
		
		foreach($spalten as $key => $spalte) {
			$setsql .= "$spalte = $werte[$key],\n";
		}
		
		$setsql = substr($setsql, 0, strlen($setsql)-2);
		
		// wheresql
		
		$wheresql = '';
		
		foreach($where as $key => $value) {
			$wheresql .= "$key = $value,\n";
		}
		
		$wheresql = substr($wheresql, 0, strlen($wheresql)-2);
		
		// Query erstellen
		
		$query = "UPDATE $table\n";
		$query .= "SET\n";
		$query .= "$setsql\n";
		$query .= "WHERE\n";
		$query .= "$wheresql;";
		
		// Query senden
		
		mysql_query($query) or die("Zeile konnte nicht bearbeitet werden\nQuery:\n<pre>$query</pre>");
		
		return true;
	}
	
	public function line_delete($table, $where) {
		
		// Parametercheck
		
		if(!isset($table) or !is_string($table))
			die('$table ist nicht definiert oder kein String');
		if(!isset($where) or !is_array($where) or count($where) > 1)
			die('$where ist nicht definiert oder kein Array oder ein zu großes Array');
		
		// wheresql
		
		$wheresql;
		
		foreach($where as $key => $value) {
			$value = mysql_real_escape_string($value);
			$wheresql .= "$key = '$value',\n";
		}
		
		$wheresql = substr($wheresql, 0, strlen($wheresql)-2);
		
		// Query erstellen
		
		$query = "DELETE FROM $table\n";
		$query .= "WHERE\n";
		$query .= "$wheresql;";
		
		// Query senden
		
		mysql_query($query) or die("Datensatz konnte nicht gelöscht werden.\nQuery:\n<pre>$query</pre>");
		
		return true;
		
	}
	
	public function cell_read($table, $spalte, $where) {
		
		// Parametercheck
		
		if(!isset($table) or !is_string($table))
			die('$table ist nicht definiert oder kein String');
		if(!isset($spalte) or !is_string($spalte))
			die('$spalte ist nicht definiert oder kein String');
		if(!isset($where) or !is_array($where))
			die('$where ist nicht definiert oder kein Array');
		
		// Spalte
		
		$select = $spalte;
		
		// From
		
		$from = $table;
		
		// Where
		
		$conditions = array();
		
		foreach($where as $column => $value) {
			$value = mysql_real_escape_string($value);
			$conditions[] = "$column = '$value'";
		}
		
		$wheresql = "WHERE ". implode(' AND ', $conditions);
		
		// Query
		
		$query = "SELECT $select FROM $from $wheresql LIMIT 1;";
		
		$daten = mysql_query($query) or die(mysql_error());
		
		// Return
		
		$row = mysql_fetch_assoc($daten);
		
		$return = $row[$select];
		
		return $return;
		
	}
	
	public function table_numrows($table, $where = '*') {
		
		if($where != '*') {
			
			$conditions = array();
			foreach($where as $column => $value) {
				$conditions[] = "$column = '$value'";
			}
			
			$wheresql = "WHERE ". implode(' AND ', $conditions);
			
		} else {
			$wheresql = '';
			}
		
		$query = "SELECT *\nFROM $table $wheresql;";
		$menge = mysql_query($query);
		$menge = mysql_num_rows($menge);
		return $menge;
	
	}
	
	public function column_create($table, $name, $typ, $notnull = false, $default = false) {
	
		if($notnull !== false)
			$notnullsql = "NOT NULL";
		else
			$notnullsql = "";
		
		if($default !== false)
			$defaultsql = "DEFAULT $default";
		else
			$defaultsql = "";
		
		switch(true) {
			
			case $typ == 'id':
			$typsql = "INT AUTO_INCREMENT PRIMARY KEY";
			break;
			
			case $typ == 'date':
			$typsql = "DATETIME";
			break;
			
			case $typ == 'int':
			$typsql = "INT";
			break;
			
			case $typ == 'smallint':
			$typsql = "SMALLINT";
			break;
			
			case $typ == 'bigint':
			$typsql = "BIGINT";
			break;
			
			case $typ == 'text':
			$typsql = "TEXT";
			break;
			
			case strstr($typ, 'string'):
			preg_match('/^string\((\d+)\)$/', $typ, $capture) or $typ == 'string' or die('$spaltentyp ist ungültig (enthält \'string\')');
			$length = $capture[1];
			empty($length) ? $length = 25 : false;
			$typsql = "VARCHAR($length)";
			break;
			
			case $typ == '':
			$typsql = "INT";
			break;
			
			default:
			$typsql = $typ;
		}
		
		$query = "ALTER TABLE $table ADD COLUMN $name $typsql $notnullsql $defaultsql;";	
		
		mysql_query($query);
		
		return true;
	
	}
	
	public function column_delete($table, $column) {
		
		$query = "ALTER TABLE $table DROP COLUMN $column;";
		
		mysql_query($query);
		
		return true;
		
	}
	
	public function tables_list() {
		
		$query = "SHOW TABLES;";
		$tables = mysql_query($query);
		
		while($row = mysql_fetch_array($tables))
			$return[] = $row[0];
			
		return $return;
		
	}

	public function tables_parent($table, $id=0) {

		// Parametercheck
		
		if(!isset($table) or !is_string($table))
			die('$table ist nicht definiert oder kein String');
		if(!isset($id) or !is_int($id))
			die('$id ist nicht definiert oder kein String');

		// Query zusammensetzen
		
		$query = "
					SELECT *
					FROM 
						(
							SELECT		
								@rownum := @rownum+1 AS rownum,
								IF(@lastid <> mylist.id, @id := mylist.id, @id) AS pathid,
								@lastid := mylist.id AS id,
								@id := (SELECT intParent FROM arbeitszeiten_kunden WHERE id = @id) AS intParent
							FROM
								(SELECT @id := 0, @lastid := 0, @rownum := 0) AS vars,
								(SELECT id FROM ".$table." AS k) AS myloop,
					            (SELECT id FROM ".$table." AS k) AS mylist
						) AS t
						INNER JOIN ".$table." AS k
							ON t.id = k.id
					WHERE pathid = ".$id;
		
		// Daten abfragen
		
		$daten = mysql_query($query) or die(mysql_error());
		
		// Return
		
		$tabelle = array();
		
		for($i = 0; $zeile = mysql_fetch_assoc($daten); $i++) {
			
			foreach($zeile as $key => $value) {
				$tabelle[$i][$key] = $value;
			}
			
		}
		
		return $tabelle;
	}	

	public function data_query($query) {
		$daten = mysql_query($query) or die(mysql_error());
		
		// Return
		
		$tabelle = array();
		
		for($i = 0; $zeile = mysql_fetch_assoc($daten); $i++) {
			
			foreach($zeile as $key => $value) {
				$tabelle[$i][$key] = $value;
			}
			
		}
		
		return $tabelle;
	}


}



	
	/* tree_query
	 * author Benedict Ernst
	 * date 21. April 2012
	 * source http://wiki.yaslaw.info/wikka/MySQLTree
	 */
?>