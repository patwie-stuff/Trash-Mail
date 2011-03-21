<?

function check_email_address($email) 
{
	if (strlen ($email) < 5) return false;

	if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) 
	{
		return false;
	}

	$email_array = explode("@", $email);
	$local_array = explode(".", $email_array[0]);

	for ($i = 0; $i < sizeof($local_array); $i++) 
	{
		if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) 
		{
			return false;
		}
	}
	if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) 
	{
		$domain_array = explode(".", $email_array[1]);
		if (sizeof($domain_array) < 2) 
		{
                	return false;
		}
		for ($i = 0; $i < sizeof($domain_array); $i++) 
		{
			if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) 
			{
				return false;
			}
		}
	}
	return true;
}

function getRandomEMails ($anz)
{
	global $maildomain;
	$mail = array ();
	$names = getRandomNames ($anz);
	foreach ($names as $name)
	{
		$entry['name']   = strtolower ($name['firstname']) . "." . 
				   strtolower ($name['lastname']);
		$entry['adress'] = $entry['name'] . "@" . $maildomain;
		$mail[] = $entry;
	}
	return $mail;
}

function getRandomNames ($anz)
{
	$ret = array ();
	$names = getNames ();
	$firstnames = array_merge ($names['malenames'], $names['femalenames']);
	shuffle ($firstnames);
	shuffle ($names['surnames']);
	for ($i = 0; $i < $anz; $i++)
	{
		$entry['firstname'] = trim ($firstnames[$i]);
		$entry['lastname'] = trim ($names['surnames'][$i]);
		$ret[] = $entry;
	}
	return $ret;
}


function getNames ()
{
	$ret = array ();
	global $malenames,$femalenames,$familynames;
	$ret['malenames'] = file($malenames);
	$ret['femalenames'] = file($femalenames);
	$ret['surnames'] = file($familynames);
	return $ret;
}

function getmsg($mbox,$mid) {
    // input $mbox = IMAP stream, $mid = message id
    // output all the following:
    global $htmlmsg,$plainmsg,$charset,$attachments;
    // the message may in $htmlmsg, $plainmsg, or both
    $htmlmsg = $plainmsg = $charset = '';
    $attachments = array();

    // HEADER
    $h = imap_header($mbox,$mid);
    // add code here to get date, from, to, cc, subject...

    // BODY
    $s = imap_fetchstructure($mbox,$mid);
    if (!isset ($s->parts) || !$s->parts)  // not multipart
        getpart($mbox,$mid,$s,0);  // no part-number, so pass 0
    else {  // multipart: iterate through each part
        foreach ($s->parts as $partno0=>$p)
            getpart($mbox,$mid,$p,$partno0+1);
    }
}

function getpart($mbox,$mid,$p,$partno) {
    // $partno = '1', '2', '2.1', '2.1.3', etc if multipart, 0 if not multipart
    global $htmlmsg,$plainmsg,$charset,$attachments;

    // DECODE DATA
    $data = ($partno)?
        imap_fetchbody($mbox,$mid,$partno):  // multipart
        imap_body($mbox,$mid);  // not multipart
    // Any part may be encoded, even plain text messages, so check everything.
    if ($p->encoding==4)
        $data = quoted_printable_decode($data);
    elseif ($p->encoding==3)
        $data = base64_decode($data);
    // no need to decode 7-bit, 8-bit, or binary

    // PARAMETERS
    // get all parameters, like charset, filenames of attachments, etc.
    $params = array();
    if (isset ($p->parameters))
        foreach ($p->parameters as $x)
            $params[ strtolower( $x->attribute ) ] = $x->value;
    if (isset ($p->dparameters))
        foreach ($p->dparameters as $x)
            $params[ strtolower( $x->attribute ) ] = $x->value;

    // ATTACHMENT
    // Any part with a filename is an attachment,
    // so an attached text file (type 0) is not mistaken as the message.
    if (isset ($params['filename']) || isset ($params['name'])) {
        // filename may be given as 'Filename' or 'Name' or both
        $filename = (isset ($params['filename']))? $params['filename'] : $params['name'];
        // filename may be encoded, so see imap_mime_header_decode()
        $attachments[$filename] = $data;  // this is a problem if two files have same name
    }

    // TEXT
    elseif ($p->type==0 && $data) {
        // Messages may be split in different parts because of inline attachments,
        // so append parts together with blank row.
        if (strtolower($p->subtype)=='plain')
            $plainmsg .= trim($data) ."\n\n";
        else
            $htmlmsg .= $data ."<br><br>";
        $charset = $params['charset'];  // assume all parts are same charset
    }

    // EMBEDDED MESSAGE
    // Many bounce notifications embed the original message as type 2,
    // but AOL uses type 1 (multipart), which is not handled here.
    // There are no PHP functions to parse embedded messages,
    // so this just appends the raw source to the main message.
    elseif ($p->type==2 && $data) {
        $plainmsg .= trim($data) ."\n\n";
    }

    // SUBPART RECURSION
    if (isset ($p->parts)) {
        foreach ($p->parts as $partno0=>$p2)
            getpart($mbox,$mid,$p2,$partno.'.'.($partno0+1));  // 1.2, 1.2.1, etc.
    }
}

function object2array($object)
{
   $return = NULL;
   if(is_array($object))
   {
       foreach($object as $key => $value)
       $return[$key] = object2array($value);
   } else {
       $var = get_object_vars($object);
       if($var)
       {
           foreach($var as $key => $value)
               $return[$key] = object2array($value);
       } else {
           return strval($object); // strval and everything is fine
       }
   }
   return $return;
}

?>