<!--

Demo PHP-class VigenereCipher
Version: 1.0, 2026-07-22
Author: Vladimir Kheifets (vladimir.kheifets.@online.de)
Copyright (c) 2026 Vladimir Kheifets All Rights Reserved

-->


<html>
<head>
<meta name="language" content="en">
<title>Demo PHP-Class VigenereCipher</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1,
user-scalable=no, user-scalable=0" >
<meta name="revisit-after" content="1 days">
<meta name="author" content="webdesign developer@alto-booking.com">
<meta name="copyright" content="Alto Booking Developer 2026">
<meta name="abstract" content="Demo PHP-class VigenereCipher">
<meta name="keywords" content="PHP-class,Vigenere cipher,entcrypted,decrypted, find key, ">
<meta name="description" content="PHP-class VigenereCipher. Entcrypted and decrypted with a known key, find key an unknown key and decrypted">
<link rel="icon" href="../favicon.ico?v=<?=time();?>" type="image/x-icon">
<link rel="stylesheet" href="index.css" >
</head>
<body>

<h1>Demo PHP-Class VigenereCipher</h1>
<h2>You can add characters to the classic Vigenere square.</h2>
<form method="post" action = "" ><input name="all" type="checkbox" value="all">Select all
<input name="extend[]" type="checkbox" value="d">Digits [ 0 &divide; 9 ]
<input name="extend[]" type="checkbox" value=" ">Space [ ]
<input name="extend[]" type="checkbox" value="-">Hyphen [ - ]
<input name="extend[]" type="checkbox" value="!">Exclamation mark [ ! ]
<input name="extend[]" type="checkbox" value="?">Question mark [ ? ]
<input name="extend[]" type="checkbox" value=".">Period [ . ]
<input name="extend[]" type="checkbox" value=",">Comma [ , ]
<input name="extend[]" type="checkbox" value="'">Apostrophe [ ' ]
<input name="extend[]" type="checkbox" value='"'>Double quote [ " ]
<hr><input name="randomKey" type="radio" value=1 checked>Apply random encryption keys
<input name="randomKey" type="radio" value=2 >Decryption with an unknown key up
<input name="randomKey" type="radio" value=3><input name="key" type="text" value='' placeholder = "Enter an encryption key (2 &divide;12 letters)" required  >
<hr><input name="inputTxt" type="radio" value=1 checked>Apply prepareid sorce texts
<input name="inputTxt" type="radio" value=2 >Enter of source text for encryption
<input name="inputTxt" type="radio" value=3 >Enter of encrypted text for decryption
<textarea name="txt" readonly></textarea><span></span>
<hr><input name="show" type="checkbox" value="1">Show Vigenere square<hr>
<input type="submit" name="send" value="Start">

</form>

<hr>
<script src="index.js"></script>

<?PHP

$chSelected=[];

if(filter_input(INPUT_POST, "send")){

$freqAlpha = json_decode(file_get_contents("freqAlphaEngExtended.json"), 1);
echo "\n<script>\n";
if(isset($_POST["show"]))
    echo "show.checked = true;";

if(isset($_POST["randomKey"])){
    $i = $_POST["randomKey"];
    $val = $_POST["key"];
    $val = str_replace(["'",'"'],["\'",'\"'],$val);
    echo "checKeyOption($i,'$val');\n";
}

if(isset($_POST["inputTxt"])){
   $i = $_POST["inputTxt"];
   $val = $_POST["txt"];
   $val = str_replace(["'",'"',PHP_EOL],["\'",'\"'," "],$val);
   echo "checkInputTxt($i,'$val');\n";
}
echo "\n</script>\n";

if(isset($_POST["extend"]))
{
    echo "<script>";
    echo "let val = [];";
    foreach($_POST["extend"] as $value)
    {
        $chSelected[]=$value;
        if($value=='"')$value = '\\"';
        ?>
        val.push("<?=$value?>");
        <?
    }
    ?>
    checked(<?=isset($_POST["all"])?>);
    </script>
    <?
}

if(!filter_input(INPUT_POST, "send"))
    exit;

if($chSelected)
{
    if(!in_array("d", $chSelected))
    {
        foreach(range("0","9") as $dig)
        {
           unset($freqAlpha[$dig]);
        }
    }


    foreach(array_diff(array_keys($freqAlpha),range("A","Z"),range("0","9")) as $key)
    {
        if(!in_array($key, $chSelected))  unset($freqAlpha[$key]);
    }
}
else
{

    foreach(array_keys($freqAlpha) as $key)
    {
        if(preg_match("/[^\p{L}]/", $key))
         unset($freqAlpha[$key]);
    }
}


############################################################
echo "<pre>";

require("VigenereCipher.php");
require("sources.php");


$cv = new VigenereCipher($freqAlpha);
$cv -> setDictionary();
$cv -> setStatNGramsFreq();

if(isset($_POST["show"]))
    $cv -> showVigenеreSquare();


$keysLenFromTo = [2,12];
$error = [];
define("debug", false);

if($inputTxt = filter_input(INPUT_POST, "inputTxt") == 3)
{
    $cv -> decryptedReport($entcryptedText, strtoupper($_POST["key"]));
}

$sourcesCount = count($sources);
$keysCount = count($randomWords);

foreach($sources as $iSorce => $source)
{
    foreach($randomWords as $key)
    {
        $keyLen = $keyLenE = strlen($key);
        $keyE = $key;

        $entcryptedText = $cv -> entcryptedReport($source, $key, $keyLen);
        $cv -> decryptedReport($entcryptedText, $key);
    }
}

}
########################################################

?>
