/*
$ix = 0;
$handle = fopen("numbers.export.csv", "r");
	while (( $data = fgetcsv($handle, 1000, ";")) !== FALSE) {
		foreach($data as $key => $value) {
			$arr[$ix][$key] = $value;
		}
		
		switch ($arr[$ix][1]) {
			case 'ordermed' :
				$intKunde = 4;
				break;
			case 'gromberg' :
				$intKunde = 10;
				break;
			case 'pw-portal' :
				$intKunde = 5;
				break;
			case 'klaviermarkt' :
				$intKunde = 12;
				break;
			case 'sesoft' :
				$intKunde = 1;
				break;
			case 'zechlin' :
				$intKunde = 2;
				break;
		}
		
		$dtmStart = date_create($arr[$ix][0].' '.$arr[$ix][2]);
		$dtmStart = date_format($dtmStart, 'Y-m-d H:i:s');
		$dtmEnde = date_create($arr[$ix][0].' '.$arr[$ix][3]);
		$dtmEnde = date_format($dtmEnde, 'Y-m-d H:i:s');
		
		$txtAufgabe = $arr[$ix][4];
		
		// $az->createAction($txtAufgabe, $intKunde, 6, $dtmStart, $dtmEnde);
		$ix++;		
	}
fclose($handle);
*/