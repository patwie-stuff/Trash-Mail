<?
$form = " <center><div id='form'> <br><form method='POST'><table>";

/* Fehlermeldungen erzeugen */
if ($valid  == 2)
{
	// Fehler bei der Eingabenvalidierung
	foreach ($validation as $vname => $validdata)
	{
		if ($validdata == 2)
		{
			$form .= "<tr><td align=left valign=top style='color:#EE0000; font-size:1.07em;' colspan='3'>";
			$form .= "Fehler: " . $validstrings[$vname] .  "<br>";
			$form .= "</td></tr>";
		}
	}
}

$form .= "
	<tr> 	<td align=right valign=center>Name</td><td></td>
		<td align=left valign=top><input type='text' name='name' value='" . $name . "' size='70'></td></tr>
        <tr class='validation" . $validation['mail'] . "'> 	
		<td align=right valign=center>E-Mail:</td><td>*</td>
		<td align=left valign=top class='inp'><input type='text' name='mail' value='" . $mail . "' size='70'></td></tr>
        <tr> <td align=right valign=top><br>Nachricht:</td><td>*</td></tr>
	<tr class='validation" . $validation['text'] . "'>	
		<td colspan=3 align=left valign=top class='inp'><textarea name='text' cols='86' rows='10'>" . $text . "</textarea>
	</td></tr>

        <tr><td align=center valign=center></td><td></td><td align=right valign=center>
	<button class='imgbutton' type='submit'><img src='/images/senden.png'></button>
	</td></tr>
	</table></form></div></center>";
?>
