<?php 

if ( isset($_POST['submit']['start']) ) {	
	$az->createAction( $_POST['txtAufgabe'], $_POST['intKunde'], 1 );
	$msg='Arbeitszeit eingetragen.';
} 
if ( isset($_POST['submit']['stop']) ) {
	$az->stopAction();
	$msg='Arbeitszeit gestoppt.';
}

?>


	<form action="index.php?u=<?=$userid;?>" method="post" accept-charset="ISO-8859-1">
		<fieldset>
			<legend>Arbeitszeit</legend>
			<div data-intro="Wähle hier deinen Kunden aus." data-position="left">
			<?php $az->displayKundenSelect('radio'); ?><br/>
			</div>
			<input type="text" name="txtAufgabe" data-intro="Tage ein, was du tust! Ein Aufgabenfeld." data-position="right"/><br/>
			<input type="submit" name="submit[start]" value="Arbeitszeit eintragen" class="start"  data-intro="LOS!" data-position="left"/>
			<input type="submit" name="submit[stop]" value="Arbeitszeit stoppen" class="stop"  data-intro="Stoppe die Arbeitszeit oder fange einen neuen Term an. Die alte Zeit wird automatisch gestoppt." data-position="right"/>
		</fieldset>
	</form>
	<a href="index.php?cid=auswertung&u=<?=$userid;?>" class="btn" target="_self" data-intro="Bekomme täglich eine Auswertung über deine Arbeitszeiten. Diese wird automatisch an abrechnung@sesoft.de geschickt." data-position="bottom">Auswertung</a>
