<?php 

class Arbeitszeiten extends sql {

	private $dataUser;
	private $dataKunden;
	private $dataZeiten;
	private $dataLog;
	
	public $dtmAktuell;

	/* __construct
	 * -----------
	 * (int $user)
	 * $this->dataUser 		enthält die Daten des aktuellen Users
	 * $this->dataKunden 	enthält die Daten des dem Users zugeordneten Kunden
	 * $this->dataZeiten 	enthält die Daten des dem Users zugeordneten Arbeitszeiten
	 * $this->dataLog 		enthält die Daten der log Tabelle
	 * $this->dtmAktuell 	enthält das aktuelle Datum im Format 'Y-m-d H:i:s'
	 */
	public function __construct($user) {
	
		// Variablen überprüfen		
		if(!isset($user) || !is_int($user))
			die('$user ist nicht gesetzt oder nicht vom Typ integer');
	
		// Datenbankdaten laden
		$this->dataUser = $this->table_read(DB_PREF.'user', '*', array('id' => $user));
		$query = "SELECT xref.intKunde AS id, k.*
					FROM ".DB_PREF."kunden AS k
					LEFT JOIN ".DB_PREF."xref_kundenxuser AS xref
						ON xref.intKunde = k.id
					WHERE xref.intUser = ".$this->dataUser[0]['id'];
		$this->dataKunden = $this->data_query($query);
		$this->dataZeiten = $this->table_read(DB_PREF.'zeiten', '*', array('intUser' => $this->dataUser[0]['id']));
		$this->dataLog = $this->table_read(DB_PREF.'log');
		$this->dtmAktuell = date('Y-m-d H:i:s');
	
	}

	public function cronjobMailAbrechnung() {
	
	  // Sendet eine Abrechnungs eMail jeden Abend, wenn Daten für den Tag vorhanden sind 
	  if(createAbrechnung()) {
	    
	  } else {
	    createLogEntry('Es wurde keine Abrechnung erstellt');
	  }
	
	}
	
	public function cronjobMailRechnung() {
	
	  // Sendet eine Rechnungs eMail jeden 1. im Monat
	  if($this->createRechnung()) {
	    
	  } else {
	    $this->createLogEntry('Es wurde keine Rechnung erstellt');
	  }
	
	}

	public function addPause() {
		// Fügt der Arbeitszeit eine Pause hinzu
	}	
	
	private function createRechnung() {
	  // generiert die Rechnung
	}
	
	private function createAbrechnung() {
	  // generiert die Tages Abrechnung, liefert die Abrechnung bei Erfolg aus - ansonsten false
	}
	
	/* createAction
	 * ---------------
	 * Die Funktion createAction speichert Einträge in die tabelle 'arbeitszeiten_zeiten'
	 * Sie überprüft ob die letzte Zeit geschlossen wurde und erzwingt andernfalls ein schließen
	 * dies wird auch geloggt.
	 * ---------------
	 * (string $txtAufgabe, int $intKunde [, int $intStatus = 1])
	 * $txtAufgabe enthält den Text der als Aufgabe gespeichert werden soll
	 * $intKunde enthält den Kunden als id
	 * $intStatus Der optionale Parameter $intStatus gibt an, unter welcher Bedingung der Eintrag gesetzt wurde
	 */
	public function createAction($txtAufgabe, $intKunde, $intStatus = 1) {
	
		// Variablen initialisieren
		$lastAction = end($this->dataZeiten);
		$currentActionWerte = array($intKunde, $txtAufgabe, $this->dtmAktuell, $intStatus, $this->dataUser[0]['id']);
		$currentActionSpalten = array('intKunde', 'txtAufgabe', 'dtmStart', 'intStatus', 'intUser');
	
		// Ist die letzte Arbeitszeit geschlossen?
		if(is_null($lastAction['dtmEnde'])) {
			$resultStopAction = $this->stopAction();
			$this->createLogEntry(__METHOD__.PHP_EOL.'Die Arbeitszeit mit der ID '.$resultStopAction.' und intUser: '.$this->dataUser[0]['id'].' wurde vom Script geschlossen.'.
									'Die nächste Aufgabe lautete: '.substr($txtAufgabe,0,120));	
		}
	
		// Fügt eine Arbeitszeit ein
		$this->line_create(DB_PREF.'zeiten', $currentActionWerte, $currentActionSpalten);
	
	}
	
/*
	public function createActionFromNumbers($txtAufgabe, $intKunde, $intStatus = 1, $dtmStart, $dtmEnde) {
	
		// Variablen initialisieren
		$currentActionWerte = array($intKunde, $txtAufgabe, $dtmStart, $dtmEnde, $intStatus, $this->dataUser[0]['id']);
		$currentActionSpalten = array('intKunde', 'txtAufgabe', 'dtmStart', 'dtmEnde', 'intStatus', 'intUser');
		
		$this->createLogEntry(__METHOD__.PHP_EOL.'Übertrag aus iWork - Numbers');
	
		// Fügt eine Arbeitszeit ein
		$this->line_create(DB_PREF.'zeiten', $currentActionWerte, $currentActionSpalten);
	
	}
*/
	
	
	
	public function stopAction($id = true) {
	
		// Variablen
		if($id) {
			$lastAction = end($this->dataZeiten);
			$id = $lastAction['id'];
		}
		$query = "UPDATE arbeitszeiten_zeiten 
					SET dtmEnde='".$this->dtmAktuell."', intStatus=4 
					WHERE id=".$id." AND intUser=".$this->dataUser[0]['id'];
		$result = mysql_query($query);		

		// Wiedergabe der ID die gestoppt wurde
		return $id;
	
	}

	/* createLogEntry
	 * ---------------
	 * Die Funktion createLogEntry speichert Einträge in die tabelle 'arbeitszeiten_log'
	 * und kann diesen auch als Mail an den User schicken, z.B.: bei besonders kritischen Szenarien.
	 * ---------------
	 * (string $txtMessage [, bool $boolMail = false])
	 * $txtMessage enthält den Text der als Logfile gespeichert werden soll
	 * $ausgabe Der optionale Parameter $boolMail gibt an, ob anschließend eine Mail an den User 
	 * 			verschickt werden soll
	 *			(a) false 	<-- standard Wert
	 *			(b) true
	 */	
	private function createLogEntry($txtMessage, $boolMail = false) {
	
	  // Variablen initisalisieren
	  $werte = array($txtMessage, $boolMail, $this->dataUser[0]['id']);
	  $spalten = array('txtMessage', 'boolMail', 'intUser');
	  
	  // Logeintrag speichern
	  $result = $this->line_create(DB_PREF.'log', $werte, $spalten);
	  
	  // Mail aussenden
	  if($boolMail) {
	    // Mail schicken
	  }
	
	}
	
	public function displayKundenSelect($type = 'select') {
	
		switch ($type) { 
			case 'select' :
				$html = '<select name="intKunde">';
				foreach($this->dataKunden as $key) {
					$html .= '<option value="'.$key['id'].'">'.$key['strName'].'</option>';
				}
				$html .= '</select>';
				break;
			case 'radio' : 
				$html = '<div class="kunden type-radio clearfix">';
				foreach($this->dataKunden as $key) {
					$html .= '<label class="cid-'.$key['id'].'"><input type="radio" name="intKunde" value="'.$key['id'].'"><span>'.$key['strName'].'</span></label>';
				}
				$html .= '</div>';
				break;
		}
		echo $html;
	
	}

	/* displayActions
	 * ---------------
	 * Die Funktion displayActions wertet die Tabelle arbeitszeiten_zeiten aus
	 * und kann auf 3 unterschiedliche Aspekte eingehen
	 * 			(1) letzte Arbeitszeit ausgeben
	 *			(2) alle Arbeitszeiten ausgeben
	 * 			(3)	alle offenen Arbeitszeiten ausgeben
	 * Optional kann die Funktion displayActions die Ausgabe
	 *			(a) als direkt Ausgabe oder 
	 * 			(b) als string Variabel zurück geben.
	 * ---------------
	 * ([string $type = 'last'] [, bool $ausgabe = true])
	 * $type Der optionale Parameter $type gibt an, auf welchen Aspekt die Funktion eingehen soll
	 *			(1) 'last' 	<-- standard Wert
	 *			(2) 'all'
	 *			(3) 'open'
	 * $ausgabe Der optionale Parameter $ausgabe gibt an, welche Rückgabe die Funktion
	 * 			wählen soll.
	 *			(a) true 	<-- standard Wert
	 *			(b) false
	 */	
	public function displayActions($type = 'last', $ausgabe = true) {
	
		switch ($type) {
			case 'last' :
				$html = '<table>';
				foreach(end($this->dataZeiten) as $key => $value) {
					$html .= '<tr><td>'.$key.'</td><td>'.$value.'</td></tr>';
				}
				$html .= '</table>';
				break;
			case 'all' :
				$html = '<table>';
				foreach($this->dataZeiten as $key => $value) {
					$html .= '<tr><td>'.$key.'</td><td><table>';
					foreach($value as $k => $v) {
						$html .= '<tr><td>'.$k.'</td><td>'.$v.'</td></tr>';
					}
					$html .= '</table></td></tr>';
				}
				$html .= '</table>';
				break;
			case 'open' :
				$html = '<table>';
				foreach($this->dataZeiten as $key => $value) {
					if(is_null($value['dtmEnde'])) { 
						$html .= '<tr><td>'.$key.'</td><td><table>';
						foreach($value as $k => $v) {
							$html .= '<tr><td>'.$k.'</td><td>'.$v.'</td></tr>';
						}
						$html .= '</table></td></tr>';
					}
				}
				$html .= '</table>';
				break;
		}
		
		// Ausgabe
		if($ausgabe) {echo $html;} else {return $html;}	
	
	}
	
	/* displayBericht
	 * ---------------
	 * Die Funktion displayBereicht zeigt Tages Auswertungen für einen beliebigen Tag, 
	 * sowie einem beliebigen Kunden dem aktuell eingeloggten User.
	 * ---------------
	 * ()
	 * 
	 */
	public function displayBericht($strDate = false, $intKunde = false, $boolKundenChilds = true) {
		
		// Variable strDate
		if(is_string($strDate)) { // Wenn strDate als String gesetzt ist
			$dtmStartLike = $strDate;
		} else {
			$dtmStartLike = date('Y-m-d');
		}
		
		// Variable intKunde
		if(is_int($intKunde)) { // Wenn intKunde als Integer gesetzt ist
			$queryWhereAdditional = " 	AND k.strName = ANY (SELECT k.strName
										FROM 
											(
												SELECT		
													@rownum := @rownum+1 AS rownum,
													IF(@lastid <> mylist.id, @id := mylist.id, @id) AS pathid,
													@lastid := mylist.id AS id,
													@id := (SELECT intParent FROM arbeitszeiten_kunden WHERE id = @id) AS intParent
												FROM
													(SELECT @id := 0, @lastid := 0, @rownum := 0) AS vars,
													(SELECT id FROM arbeitszeiten_kunden AS k) AS myloop,
										            (SELECT id FROM arbeitszeiten_kunden AS k) AS mylist
											) AS t
											INNER JOIN arbeitszeiten_kunden AS k
												ON t.id = k.id
										WHERE pathid = ".$intKunde.")";	
		} else {
			$queryWhereAdditional = '';
		}
		
		
		// Daten auswählen
		$query = "SELECT DATE_FORMAT(z.dtmStart, '%d.%m.%Y') AS dtmDatum,
						k.strName AS strKunde, 
						z.txtAufgabe AS strAufgabe, 
						((HOUR(TIMEDIFF(z.dtmEnde, z.dtmStart))*60) + MINUTE(TIMEDIFF(z.dtmEnde, z.dtmStart))) AS intMinuten,
						k.intSatz AS intGehaltStunde
					FROM arbeitszeiten_zeiten AS z
					LEFT JOIN arbeitszeiten_kunden AS k
						ON k.id = z.intKunde
					WHERE z.dtmStart LIKE '".$dtmStartLike."%' AND intUser=".$this->dataUser[0]['id'].$queryWhereAdditional."
					GROUP BY strKunde, strAufgabe";
		$dataBericht = $this->data_query($query);
		foreach($dataBericht AS $k => $v) {
			foreach($v AS $key => $value) {
				if($key=='intMinuten') {
					$arr[$v['strKunde']][$key] += $value;
				} elseif($key=='strAufgabe') {
					$arr[$v['strKunde']][$key] .= '<li>' .$value. '</li>';
				} else {
					$arr[$v['strKunde']][$key] = $value;
				}
			}
		}
		
		// Tabelle erstellen
		$html  = "<table>
			<tr><th>Tag</th><th>Kunde</th><th>Aufgabe</th><th>Minuten</th><th>Satz</th><th style='text-align: right;'>Gesamt</th></tr>";	
		foreach($arr as $k => $v) {
			$html .= "<tr>";
			foreach($v as $key => $value) {
				$html .= "<td><ul>".$value."</ul></td>";
			}
			$html .= "<td style='text-align: right;'>".round(($v['intGehaltStunde']/60)*$v['intMinuten'], 2)." Euro</td>";
			$html .="</tr>".  PHP_EOL;
		}

		$html .= "</table>";
		
		// Ausgabe
		echo $html;	
	
	}
	
	
	/* statusEditieren
	 * ---------------
	 * Die Funktion statusEditieren editiert den Status und 
	 * protokolliert die Änderung in 'arbeitszeiten_log'
	 * Es wird in der Tabelle (standardmäßig 'arbeitszeiten_zeiten') stets versucht
	 * die Spalte 'intStatus' zu ändern.
	 * ---------------
	 * (int $id, int $neuerStatus [, string $table = 'zeiten'] [, string $spalte = 'intStatus'])
	 * $id Die ID, bei der der Status geändert werden soll
	 * $neuerStatus Der neue Status
	 * $table Der optionale Parameter $table gibt an, in welcher Tabelle die ID zu finden ist. 
	 */
	public function statusEditieren($id, $neuerStatus, $table = 'zeiten', $spalte = 'intStatus') {
		
		// Variablen überprüfen
		if(!isset($id) || !is_int($id))
			die('$id ist nicht gesetzt oder nicht vom Typ integer');
		if(!isset($neuerStatus) || !is_int($neuerStatus))
			die('$neuerStatus ist nicht gesetzt oder nicht vom Typ integer');
		if(!is_string($table))
			die('$table ist nicht vom Typ string');
		if(!is_string($spalte))
			die('$spalte ist nicht vom Typ string');
		
		// Log Text schreiben
		$txtLog = __METHOD__.PHP_EOL.
					'Tabelle: '.DB_PREF.$table.PHP_EOL.
					'ID: '.$id.PHP_EOL.
					'Neuer Status: '.$neuerStatus;
		
		// Alten Status laden
		if($table = 'zeiten') { // nur wenn $table = 'zeiten' ist
			$alterStatus = $this->cell_read(DB_PREF.$table, $spalte, array('id'=>$id));
			$txtLog .= PHP_EOL.'Alter Status: '.$alterStatus;
		}
			
		// Datenbank aktualisieren
		$this->line_edit(DB_PREF.$table, array("id"=>$id), array($neuerStatus, $this->dtmAktuell), array($spalte, dtmUpdated));

		// Log schreiben
		$this->createLogEntry($txtLog);
		
		// return
		return true;
	
	}
}

?>