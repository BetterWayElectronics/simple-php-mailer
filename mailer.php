<?php
$ch = curl_init();
$privatekey = "Your Google API Private Key";
curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'secret' => $privatekey,
    'response' => $_POST['g-recaptcha-response'],
    'remoteip' => $_SERVER['REMOTE_ADDR']
]);


$resp = json_decode(curl_exec($ch));
curl_close($ch);
$valid = 1;

//Filters
$email = $_POST['email'];
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { //Works well but is not flawless
  	$valid = 0;
	echo "<script>
	alert('Invalid E-Mail Address');
	window.location.href='https://yourwebsite.www';
	</script>";
}

$name = $_POST['name'];
if (!preg_match("/^[a-zA-Z ]*$/",$name)) {
	$valid = 0;
	echo "<script>
	alert('Name Invalid!');
	window.location.href='https://yourwebsite.www';
	</script>";
}

$number = $_POST['number'];
if (!preg_match("/[0][4][0-9]{8}$/",$number)) { //Australian numbers only in this case.
	$valid = 0;
	echo "<script>
	alert('Number Invalid!');
	window.location.href='https://yourwebsite.www';
	</script>";
}

$message = $_POST['message'];
if (preg_match('/https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()!@:%_\+.~#?&\/\/=]*)/', $message) || preg_match('/^(.*?(\bhttps\b)[^$]*)$/', $message) || preg_match('/^(.*?(\bhttp\b)[^$]*)$/', $message) || preg_match('/^(.*?(\bwww\b)[^$]*)$/', $message) || preg_match('/^(.*?(\bhref\b)[^$]*)$/', $message)) {
  	$valid = 0;
	echo "<script>
	alert('Unsolicited URLs Not Permitted!');
	window.location.href='https://yourwebsite.www';
	</script>";
}

//Get User Agent Info
$user_agent = $_SERVER['HTTP_USER_AGENT'];

function getOS() { 

    global $user_agent;

    $os_platform  = "Unknown OS Platform";

    $os_array     = array(
                          '/windows nt 10/i'      =>  'Windows 10',
                          '/windows nt 6.3/i'     =>  'Windows 8.1',
                          '/windows nt 6.2/i'     =>  'Windows 8',
                          '/windows nt 6.1/i'     =>  'Windows 7',
                          '/windows nt 6.0/i'     =>  'Windows Vista',
                          '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
                          '/windows nt 5.1/i'     =>  'Windows XP',
                          '/windows xp/i'         =>  'Windows XP',
                          '/windows nt 5.0/i'     =>  'Windows 2000',
                          '/windows me/i'         =>  'Windows ME',
                          '/win98/i'              =>  'Windows 98',
                          '/win95/i'              =>  'Windows 95',
                          '/win16/i'              =>  'Windows 3.11',
                          '/macintosh|mac os x/i' =>  'Mac OS X',
                          '/mac_powerpc/i'        =>  'Mac OS 9',
                          '/linux/i'              =>  'Linux',
                          '/ubuntu/i'             =>  'Ubuntu',
                          '/iphone/i'             =>  'iPhone',
                          '/ipod/i'               =>  'iPod',
                          '/ipad/i'               =>  'iPad',
                          '/android/i'            =>  'Android',
                          '/blackberry/i'         =>  'BlackBerry',
                          '/webos/i'              =>  'Mobile'
                    );

    foreach ($os_array as $regex => $value)
        if (preg_match($regex, $user_agent))
            $os_platform = $value;

    return $os_platform;
}

function getBrowser() {

    global $user_agent;

    $browser        = "Unknown Browser";

    $browser_array = array(
                            '/msie/i'      => 'Internet Explorer',
                            '/firefox/i'   => 'Firefox',
                            '/safari/i'    => 'Safari',
                            '/chrome/i'    => 'Chrome',
                            '/edge/i'      => 'Edge',
                            '/opera/i'     => 'Opera',
                            '/netscape/i'  => 'Netscape',
                            '/maxthon/i'   => 'Maxthon',
                            '/konqueror/i' => 'Konqueror',
                            '/mobile/i'    => 'Handheld Browser'
                     );

    foreach ($browser_array as $regex => $value)
        if (preg_match($regex, $user_agent))
            $browser = $value;

    return $browser;
}

//If Captcha Valid
if ($resp->success && $valid == 1) {
	$name = $_POST['name'];
	$email = $_POST['email'];
	$message = $_POST['message'];
	$to = 'enquiry@yourwebsite.www'; 
	$subject = "Your Website Enquiry (" . $_POST['name'] . ")";
	$number = $_POST['number'];
	$ip = $_SERVER['REMOTE_ADDR'];
	$hostname = gethostbyaddr($ip);
	$refer = $_SESSION['org_referer'];
	$user_os        = getOS();
	$user_browser   = getBrowser();
	
   	$headers = "From: $name <$email>\r\n".
               "MIME-Version: 1.0" . "\r\n" .
               "Content-type: text/html; charset=UTF-8" . "\r\n"; 
			
        $body = "Name: $name<br>E-Mail: $email<br>Number: $number<br>IP: $ip ($hostname)<br>OS: $user_os<br>Browser: $user_browser<br>Referer: $refer<br>Message:<br><br> $message";

	mail ($to, $subject, $body, $headers);
	
  //When the email is sent do this
	echo "<script>
	alert('Message Sent!');
	window.location.href='https://yourwebsite.www';
	</script>";
	die();
	
} else {
	echo "<script>
	alert('Verification Failed. Please Try Again!');
	window.location.href='https://yourwebsite.www';
	</script>";
}
