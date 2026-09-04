# PHP class VigenereCipher

### Version: 1.0, 2026-07-22

Author: Vladimir Kheifets <vladimir.kheifets.@online.de>

Copyright &copy; 2026 Vladimir Kheifets All Rights Reserved

The VigenereCipher class provides encryption and decryption of English text using the Vigenère cipher. It supports operations with a known key, as well as key recovery (breaking the cipher). The class supports both the classic Vigenère square and an extended version that includes digits and special characters: spaces, hyphens, exclamation marks, question marks, periods, commas, apostrophes, and double quotes.

#### Demo:
[https://www.alto-booking.com/developer/VigenereCipher/](https://www.alto-booking.com/developer/VigenereCipher/)

#### Example (extended Vigenere square):
>
>**Source text:**
>
>Text length: 314
>
>enigma m4 message: P1030685.
Commanding Admiral of Submarines, S.M.H.S. East, and Comsubs East from Chief of 4th Submarine Flotilla: Weighed anchor in Swinemünde. Cruise to Hörup-Haff begun. Have stations in Swinemünde.  Addition for Control: G.F. hands over remote-station traffic as of 2000 hours on the 1st.
>
>Entcrypted with key: ENIGMA (length: 6)
>
>**Entcrypted text:**
>
>q80u6ie7cg6m4'qu0:a1e,?a,cio
Q8u0v3r2vsnQr6q3v1g8ne' p6i333s"ee'oZsPk'og0i4,mguvpnS26z6w8g0i4,it!w0nSv2mrn4tm"52i6,j0v7w7meZ12'qy6q:g.mu1ysyam8sv8xe33g"4u8uzü7lqtiQ!2u'ug'we2ö789fTvvtmjq1 1saTv!smz5v9w8v4n01mZ833s6üvpxogmIp0072wxnv2!aO937!wy:nWmXge2q1yze9!s!a3x22'mj'9o'qz8i7!irz0qmi4n4tm  b,g1w6"8g8ve,ysm94,o
>
>Decrypted with a known key: ENIGMA
>
>**Decrypted text:**
>
>enigma m4 message: p1030685.
Commanding Admiral of submarines, s.M.H.s. east, and Comsubs east from Chief of 4th submarine Flotilla: weighed anchor in swinemünde. Cruise to hörup-Haff begun. Have stations in Swinemünde.  Addition for Control: G.F. hands over remote-station traffic as of 2000 hours on the 1st.
>
>
>**Cipher breaking (Frequency Analysis):**
>
>The cipher text was analyzed using frequency analysis
>with an unknown key length **ranging from 2 to 12 characters**.
>**Recovered key:** ENIGMA (length: 6)
>
>**Statistical cryptanalysis of decrypted text**
>
>**Index of coincidence (IC):** 0.06255
>
>Analysis of the trigrams frequency
>
>**Standard deviation (SD):** 0.0077
>
>**Interquartile Range (IQR):** 0.00434
>
>**Decrypted text:**
>
>enigma m4 message: p1030685.
>Commanding Admiral of submarines, s.M.H.s. east, and Comsubs east from Chief of 4th submarine Flotilla: weighed anchor in swinemünde. Cruise to hörup-Haff begun. Have stations in Swinemünde.  Addition for Control: G.F. hands over remote-station traffic as of 2000 hours on the 1st.
>
>[Source text for entcrypted - Translation (preliminary)](https://enigma.hoerenberg.com/index.php?cat=The%20U534%20messages&page=P1030685)
>
>



### 1. PHP-class VigenereCipher

```php
<?PHP
    require("VigenereCipher.php");

    $cv = new VigenereCipher($freqAlpha, $languageIC);

    $freqAlpha - (array|null) - Letter frequency
    by null - Relative frequency in the english language;

    $languageIC - (float|null) - index of coincidence for the encryption language
    by null - $languageIC = 0.0667 (for english language);

?>
```

### File VigenereCipher.php

```php
<?PHP

/*
PHP-class VigenereCipher
Version: 1.0, 2026-07-22
Author: Vladimir Kheifets (vladimir.kheifets.@online.de)
Copyright (c) 2026 Vladimir Kheifets All Rights Reserved
*/

class VigenereCipher{

    protected $alpha;
    protected $alphaF;
    protected $alphaLen;
    protected $VigenereTableEnt;
    protected $VigenereTableDec;
    protected $textLen;
    protected $textLenOnlyL;
    protected $keyRepeat;
    static $dictionary;
    static $statNGramsFreq;
    static $languageIC;
    static $languageICmin;

    //only letters of the english alphabet
    static $nAlp = "^a-zA-Z";
    static $L = "[a-zA-Z]";
    static $Ll = "[a-z]";
    static $nL;

    /*
    letters of the english and international alphabets
    static $nAlp = "\P{L}";
    static $L = "\p{L}";
    static $Ll = "\p{Ll}";
    */

    function __construct($freqAlpha=
        [
            'A'=>0.08167,'B'=>0.01492,'C'=>0.02782,'D'=>0.04253,'E'=>0.12702,
            'F'=>0.02228,'G'=>0.02015,'H'=>0.06094,'I'=>0.06966,'J'=>0.00153,
            'K'=>0.00772,'L'=>0.04025,'M'=>0.02406,'N'=>0.06749,'O'=>0.07507,
            'P'=>0.01929,'Q'=>0.00095,'R'=>0.05987,'S'=>0.06327,'T'=>0.09056,
            'U'=>0.02758,'V'=>0.00978,'W'=>0.02360,'X'=>0.00150,'Y'=>0.01974,
            'Z'=>0.00074
        ], $languageIC = 0.0667){

        $alpha = array_keys($freqAlpha);

        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


        preg_match_all("/\P{L}/", implode("",$alpha), $match);
        $countMatch = count($match[0]);
        if($countMatch > 1)
        {
            $tmp = array_diff($match[0], range(0,9));

            $buf=[];

            if( ($countMatch - count($tmp)) == 10){
                $buf[] = "\d";
            }

            foreach($tmp as $ch){
                $buf[] = "\\".$ch;
            }

            $subPattern = empty($buf)?"":"|".implode("|",$buf);
        }
        else
            $subPattern = "";

        self::$nL = "/[".self::$nAlp."]/";
        self::$nAlp = "/[".self::$nAlp.$subPattern."]/";
        self::$L = "/(".self::$L.$subPattern.")/";
        self::$Ll = "/(".self::$Ll.$subPattern.")/";

        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


        $d = $alpha;
        foreach($alpha as $i=>$ch)
        {
            if($i>0){
                $t = $d[0];
                array_shift($d);
                $d[] = $t;
            }
            $VigenereTableEnt[$ch] = array_combine($alpha, $d);
            $VigenereTableDec[$ch] = array_flip($VigenereTableEnt[$ch]);
        }

        $this-> freqAlpha = $freqAlpha;
        $this-> alpha = $alpha;
        $this-> alphaLen = count($alpha);
        $this-> alphaF = array_flip($alpha);
        self::$languageIC = $languageIC;
        self::$languageICmin = (float) bcdiv($languageIC, 1, 2);
        $this-> VigenereTableEnt = $VigenereTableEnt;
        $this-> VigenereTableDec = $VigenereTableDec;
    }

    //---------------------------------------------------------------------------

    public function setKey($key, $textLen = null, $textLenOnlyL = null){

        if(!$textLen)
        {
            $textLen = $this -> textLen;
            $textLenOnlyL= $this -> textLen;
        }

        $keyLen = strlen($key);
        $keyRepeat = substr(str_repeat($key, ceil($textLenOnlyL/$keyLen)), 0, $textLen);
        $this -> keyRepeat = $keyRepeat;
        return $keyRepeat;
    }

    //---------------------------------------------------------------------------

    public function showVigenеreSquare(){
        $VigenereTableEnt = $this -> VigenereTableEnt;
        echo   "<p align=center><h1>Vigenеre square</h1><table border=1 align=center>";
        echo   "<tr><td >&nbsp;</td><td class=rand>", implode("",array_keys($VigenereTableEnt)),"</td></tr>";
        foreach($VigenereTableEnt as $key=>$row){
            echo  "<tr><td class=rand>$key</td><td>", implode("",array_values($row)),"</td></tr>";
        }
        echo  "</table></p><br><hr><br>";
    }

    //---------------------------------------------------------------------------

    public function entcrypter($key, $source){

        $VigenereTableEnt = $this -> VigenereTableEnt;
        $sourceLen = strlen($source);
        $onlyLSource = preg_replace(self::$nAlp, "", $source);
        $onlyLSourceLen = strlen($onlyLSource);

        $keyRepeat = $this -> setKey($key, $sourceLen, $onlyLSourceLen);

        //if(debug) echo  "$onlyLSource<br>$source<br>$keyRepeat<br>";

        $entText = "";
        $iKey=0;
        for ($i=0; $i < $sourceLen; $i++) {
           $sourceLetter = $source[$i];
           if(preg_match(self::$L, $sourceLetter))
           {
               $keyLetter = $keyRepeat[$iKey];
               if(preg_match(self::$Ll, $sourceLetter))
               {
                    $sourceLetter = strtoupper($sourceLetter);
                    if(isset($VigenereTableEnt[$keyLetter][$sourceLetter]))
                        $entLetter = strtolower($VigenereTableEnt[$keyLetter][$sourceLetter]);
                    else
                        $entLetter = $sourceLetter;
               }
               else
               {
                    if(isset($VigenereTableEnt[$keyLetter][$sourceLetter]))
                        $entLetter = $VigenereTableEnt[$keyLetter][$sourceLetter];
                    else
                        $entLetter = $sourceLetter;
               }
               $iKey++;
           }
           else
           {
                $entLetter = $sourceLetter;
           }
           $entText .= $entLetter;
        }
        return $entText;
    }

    //---------------------------------------------------------------------------

    public function decrypter($entText, $key, $outputAllSolutions = false ){

        $clearText = strtoupper(preg_replace(self::$nAlp, "", $entText));
        $clearTextLen = strlen($clearText);
        if(debug) echo  "clearTextLen: $clearTextLen\n";
        if(is_array($key))
        {
            $keysLen = $keysLen2 = range(...$key);
            $resDec = [];

            if(debug) echo "calc Keylengths by Friedman\n";
            $keysLen = $keysLen2 = range(...$key);
            $keyLenIC = $this-> calcKeylengthsByFriedman($clearText, $clearTextLen, $keysLen);
            if(debug) print_r($keyLenIC);
            if(debug) print_r($keysLen);
            $resDec = $this -> keylengthsToKeyDec($entText, $clearText, $keysLen, $outputAllSolutions);

            if(count($resDec) == 0)
            {
                if(debug) echo "calc Keylengths by Kasiski\n";
                $keysLen = $keysLen2;
                $distFactotor = $this-> calcKeylengthsByKasiski($clearText, $clearTextLen, $keysLen);
                if(debug) print_r($distFactotor);
                if(debug) print_r($keysLen);
                $resDec = $this -> keylengthsToKeyDec($entText, $clearText, $keysLen, $outputAllSolutions);
            }
            return  $resDec;
        }
        else
        {
            $VigenereTableDec = $this -> VigenereTableDec;

            $entTextLen = strlen($entText);

            $keyRepeat = $this -> setKey($key, $entTextLen, $clearTextLen);


            $decText = "";
            $iKey = 0;
            for ($i=0; $i < $entTextLen; $i++) {

               $entTextLetter = $entText[$i];

               if(preg_match(self::$L, $entTextLetter))            {

                   $keyLetter = $keyRepeat[$iKey];
                   if(preg_match(self::$Ll, $entTextLetter))
                   {
                        $entTextLetter = strtoupper($entTextLetter);
                        if(isset($VigenereTableDec[$keyLetter][$entTextLetter]))
                            $decLetter = strtolower($VigenereTableDec[$keyLetter][$entTextLetter]);
                        else
                            $decLetter = $entTextLetter;
                   }
                   else
                   {
                        if(isset($VigenereTableDec[$keyLetter][$entTextLetter]))
                            $decLetter = $VigenereTableDec[$keyLetter][$entTextLetter];
                        else
                            $decLetter = $entTextLetter;
                   }
                   $iKey++;
               }
               else
               {
                    $decLetter = $entTextLetter;
               }
               $decText .= $decLetter;
            }
            return $decText;
        }
    }

    //---------------------------------------------------------------------------
    private function keylengthsToKeyDec($entText, $clearText, $keysLen, $outputAllSolutions=false){

        $output =  [];

        $languageICmin = self::$languageICmin;
        foreach ($keysLen as $keyLen)
        {
            $key = $this ->  findKey($clearText, $keyLen);
            $decText = $this -> decrypter($entText, $key);
            $IC = $this -> getIndexCoincidence($decText);

            $tmp = compact("decText", "key", "keyLen", "IC");
            if(debug) echo  "184: $keyLen $key  $IC \n $decText<hr>";
            if($outputAllSolutions)
            {
                $output[] = $tmp;
            }
            else if($IC > $languageICmin)
            {
                if($keyLen>3 AND $keyLen % 2 == 0){
                    $likelyKeyLen  = $keyLen / 2;
                    $likelyKey = substr($key, 0, $likelyKeyLen );
                    if($likelyKey === substr($key, $likelyKeyLen ))
                    {
                        $key = $likelyKey;
                        $keyLen = $likelyKeyLen ;
                        $tmp = compact("decText", "key", "keyLen", "IC");
                    }
                }

                $output[] = $tmp;
                if(debug) echo  "191: $keyLen $key $IC \n";
                break;
            }

        }
        return $output;
    }
    //---------------------------------------------------------------------------
    public function getIndexCoincidence($entText){

        $entTextU = strtoupper(preg_replace(self::$nL, "", $entText));
        $textLen = strlen($entTextU);
        $rankLetters = array_count_values(str_split($entTextU));
        $L = $textLen * ($textLen - 1);
        $indSum=0;
        foreach($rankLetters as $letter => $n){
            $ind =  ($n*($n-1))/$L;
            $indSum += $ind;
        }
        return round($indSum,5);
    }

    //---------------------------------------------------------------------------

    public function calcKeylengthsByFriedman($entTextU, $entTextUlen, &$keysLen ){

        $languageICmin =  self::$languageICmin;

        $ICkeys = [];

        foreach ($keysLen as $iKey => $keyLen)
        {
            $counter = 0;
            $block = "";
            for ($i = 0; $i < $entTextUlen; $i++)
            {
                if( ++$counter % $keyLen == 0 )
                    $block .= $entTextU[$i];
            }

            $IC = $this -> getIndexCoincidence($block);
            if($IC>0)
                $keysLenIC[$keyLen] = $IC;

        }

        $minDeviation = - INF;
        $meanIC = array_sum($keysLenIC) / count($keysLenIC);

        $deviations = [];

        foreach ($keysLenIC as $keyLen => $IC)
        {
            $deviation = $IC - $meanIC;
            if($deviation <= 0 AND $deviation > $minDeviation)
                $minDeviation = $deviation;
            $deviations[$keyLen] = $deviation;
        }

        $keysLen = [];

        foreach($keysLenIC as $keyLen=>$IC){
            if($deviations[$keyLen] >= $minDeviation OR $IC >= $languageICmin)
                $keysLen[]=$keyLen;
            else
                unset($keysLenIC[$keyLen]);
        }

        return $keysLenIC;
    }
    //-----------------------------------------------------
    public function calcKeylengthsByKasiski($entTextU, $entTextUlen, &$keysLen,  $ngramLength = 3){
        if($entTextUlen<200 AND $ngramLength==3) $ngramLength = 2;
        $positions = [];

        for ($i = 0; $i <= $entTextUlen - $ngramLength; $i++) {
            $ngram = mb_substr($entTextU, $i, $ngramLength);
            $positions[$ngram][] = $i;
        }

        $distances = [];
        foreach ($positions as $ngram => $posList) {
            $countPosList = count($posList);
            if ($countPosList > 1)
            {
                for ($i = 0; $i < $countPosList - 1; $i++)
                {
                    for ($j = $i + 1; $j < $countPosList; $j++)
                    {
                        $dist = $posList[$j] - $posList[$i];
                        $distances[] = $dist;
                    }
                }
            }
        }

        $factors = [];
        foreach ($distances as $d) {
            for ($f = 2; $f <= $d; $f++) {
                if ($d % $f === 0) {
                    $factors[$f] = ($factors[$f] ?? 0) + 1;
                }
            }
        }

        arsort($factors);
        $maxKeysLen = max($keysLen);

        foreach($factors as $key=>$val)
            if($key > $maxKeysLen) unset($factors[$key]);
        $keysLen = array_keys($factors);

        return [
            'distances' => $distances,
            'factors'   => $factors
        ];
    }
    //-----------------------------------------------------

    public function chiSquare($text) {
        $freqAlpha = $this->freqAlpha;
        $n = strlen($text);
        $counts = array_count_values(str_split($text));
        $chi = 0.0;

        foreach ($freqAlpha as $letter => $expectedFreq) {
            $observed = $counts[$letter] ?? 0;
            $expected = $expectedFreq * $n;
            $chi += ($observed - $expected) ** 2 / ($expected + 1e-9);
        }

        return $chi / ($n ?: 1);
    }

    //-----------------------------------------------------

    public function decrypterCaesar($cipher, $shift) {
        $alpha = $this-> alpha;
        $alphaLen = $this-> alphaLen;
        $result = "";
        //if(debug) echo  "<hr>$cipher > $shift<br>";
        foreach (str_split($cipher) as $c) {
            $pos = array_search($c, $alpha);
            $newPos = ($pos - $shift + $alphaLen) % $alphaLen;
            //if(debug) echo  "36:$c  $pos > $newPos<br>";
            $result .= $alpha[$newPos];
        }
        return $result;
    }

    //-----------------------------------------------------

    public function findKey($ciphertext, $keyLen) {
        $alpha = $this-> alpha;
        $alphaLen = $this-> alphaLen;
        $freqAlpha = $this->freqAlpha;

        // clean text

        $text = strtoupper(preg_replace(self::$nAlp, "", $ciphertext));


        // split into columns
        $columns = [];
        for ($i = 0; $i < $keyLen; $i++) {
            $columns[$i] = "";
            for ($j = $i; $j < strlen($text); $j += $keyLen) {
                $columns[$i] .= $text[$j];
            }
        }

        $key = "";

        foreach ($columns as $iCol =>$col) {
            $bestShift = 0;
            $bestScore = INF;

            for ($shift = 0; $shift < $alphaLen; $shift++) {
                $dec = $this -> decrypterCaesar($col, $shift);
                $score = $this -> chiSquare($dec, $freqAlpha);

                if ($score < $bestScore) {
                    $bestScore = $score;
                    $bestShift = $shift;
                }

            }

            //if(debug) echo  "$iCol:<br>$col<br>$dec<br>score: $score bestScore: $bestScore  shift: $shift bestShift: $bestShift ", $alpha[$bestShift],"<br>";

            $key .= $alpha[$bestShift];
        }

        return $key;
    }

    //-----------------------------------------------------

    public function setDictionary($la = "eng"){
        self::$dictionary = file_get_contents("dictionaries/".$la."Dictionary.txt");
    }

    //-----------------------------------------------------
    static function arrayChangeValueCase(&$arr, $wordInUperCase=true){
        foreach($arr as $i => $val)
           $arr[$i] = $wordInUperCase?strtoupper($val):strtolower($val);
        $arr = array_unique($arr);
        return count($arr) == 1;
    }

    //-----------------------------------------------------

    public function cribDraggingDecrypter($word){
        $pattB = "~\b";
        $pattE = "\b~iu";
        $wordInUperCase = strtoupper($word) === $word;
        //~~~~~~~~~~~~~~~~~~~~~~~~~~~
        /*
        $wordPatern = $word;
        preg_match_all($pattB.$wordPatern.$pattE,  self::$dictionary, $m);
        if(count($m[0]) == 1){
            return 1;
        }
        */
        //~~~~~~~~~~~~~~~~~~~~~~~~~~~

        $subPattern = [];
        for($i=0; $i<strlen($word); $i++){
            $tmp = $word;
            $tmp[$i] = ".";
            $subPattern[] = $tmp;
        }
        $wordPatern = "(".implode("|",$subPattern).")";
        preg_match_all($pattB.$wordPatern.$pattE,  self::$dictionary, $m[0]);
        //print_r($m[0]);
        if( self::arrayChangeValueCase($m[0][0]) )
        {
            return $m[0][0];
        }

        //~~~~~~~~~~~~~~~~~~~~~~~~~~~
        $i=0;
        $wordLen = strlen($word);
        $subPattern = $word[0];
        while($i < $wordLen){
            $k = strlen($subPattern);
            $wordPatern = str_pad($subPattern, $wordLen, ".");
            preg_match_all($pattB.$wordPatern.$pattE,  self::$dictionary, $m[1]);

            if(!$m[1][0])
            {
                $subPattern[$k-1] = ".";
            }
            else If(self::arrayChangeValueCase($m[1][0]))
                break;
            else
            {
                $i += 1;
                if(isset($word[$i]))
                    $subPattern .= $word[$i];
                else
                    break;
            }
        }

        //~~~~~~~~~~~~~~~~~~~~~~~~~~~
        if(count($m[1][0]) == 1 )
            return $m[1][0];
        else
            return array_unique(array_merge($m[0][0], $m[1][0]));
    }

    //-----------------------------------------------------

    public function setStatNGramsFreq($n=3, $la = "eng"){
        self::$statNGramsFreq[$n] = json_decode(file_get_contents($la.$n."Grams.json"), 1);
    }
    //-----------------------------------------------------

    public function getNgrams($txt, $n=3){
        $txt = strtolower(preg_replace(self::$nL, "", $txt));
        $NGrams = [];
        $length = mb_strlen($txt, 'UTF-8');
        $length = $n>1?$length-$n:$length-1;
        for ($i = 0; $i <= $length; $i++)
            $NGrams[] = mb_substr($txt, $i, $n);
        return $NGrams;
    }

    //-----------------------------------------------------

    public function getTextFromNgrams($NGrams) {
      $txt = $NGrams[0];
      $iBegin = mb_strlen($txt)-1;
      unset($NGrams[0]);
      foreach($NGrams as $item){
        $txt .= mb_substr($item, $iBegin);
      }
      return $txt;
    }

    //-----------------------------------------------------

    public function getNGramsFreq($txt, $n=3){
        $NGramsFreq = [];
        $NGrams = $this->getNgrams($txt, $n);
        foreach($NGrams as $NGram){
            $freq = self::$statNGramsFreq[$n][$NGram] ?? 0;
            $NGramsFreq[$NGram] = $freq;
        }
        return  $NGramsFreq;
    }

//-----------------------------------------------------

    public function  getDeviationStats($data){
        $sum = array_sum($data);
        $count = count($data);
        $mean = $sum/$count;
        $squaredDeviationsSum = 0;

        foreach($data as $key => $value){
            $deviation = $value - $mean;
            $deviations[$key] = $deviation;
            $squaredDeviationsSum += pow($deviation, 2);
        }

        $divisor = $count - 1;
        $variance = $divisor > 0 ? ($squaredDeviationsSum / $divisor) : 0.0;
        $standardDeviation = sqrt($variance);
        return (object) compact("variance", "standardDeviation", "deviations", "mean" );
    }

//-----------------------------------------------------

    public function getQuantileStats($data, $getMedian = false) {
        $count = count($data);
        if ($count < 2) return 0;
        if($getMedian)
        {
            $middle = floor(($count - 1) / 2);
            if ($count % 2 !== 0)
            {
                return $data[$middle];
            }
            else
            {
                return ($data[$middle] + $data[$middle + 1]) / 2;
            }
        }

        sort($data);

        $middle = floor($count / 2);

        if ($count % 2 !== 0) {
            $lowerHalf = array_slice($data, 0, $middle);
            $upperHalf = array_slice($data, $middle + 1);
        }
        else
        {
            $lowerHalf = array_slice($data, 0, $middle);
            $upperHalf = array_slice($data, $middle);
        }

        $Q1 = self::getQuantileStats($lowerHalf, 1);
        $Q3 = self::getQuantileStats($upperHalf, 1);
        $Q2 = self::getQuantileStats($data, 1);
        $IQR = $Q3 - $Q1;
        $min = min($data);
        $max = max($data);
        $range = end($data)-$data[0];
        return (object) compact("Q1","Q2","Q3","IQR","min","max","range");
    }
//-----------------------------------------------------
private function NGramsFreqToStats($txt){
    $NGramsFreq = $this -> getNGramsFreq($txt);
    $SD = $this -> getDeviationStats($NGramsFreq) -> standardDeviation;
    $IQR = $quantileStats = $this -> getQuantileStats($NGramsFreq)-> IQR;
    return compact("SD", "IQR");
}
//-----------------------------------------------------

    public function entcryptedReport($source, $key, $keyLen){

        $entcryptedText = $this -> entcrypter($key, $source);
        $sourceLen = strlen($source);
        echo <<<HTML

        <b>Source text:</b>
        <b>Text length:</b> $sourceLen
        <div>$source</div>

        <b>Entcrypted with key:</b> $key (length: $keyLen)
        <b>Entcrypted text:</b>
        <div>$entcryptedText</div>

        HTML;
        return $entcryptedText;
    }

//-----------------------------------------------------

    public function decryptedReport($entcryptedText, $key=null){

    global $keysLenFromTo;
        if($_POST['inputTxt'] == 3 )
        {
            $entcryptedLen = strlen($entcryptedText);
            echo <<<HTML
            <b>Entcrypted text:</b>
            <div>$entcryptedText</div>
            <b>Text length:</b> $entcryptedLen

            HTML;
        }

       if($key)
       {
           $decryptedText = $this -> decrypter($entcryptedText, $key );

           echo <<<HTML

           <b>Decrypted with a known key:</b> $key
           <b>Decrypted text:</b>
           <div>$decryptedText</div>

           HTML;
       }

       $keyE = $key;

       $resDecrypter = $this -> decrypter($entcryptedText, $keysLenFromTo);

           foreach($resDecrypter as  $val)
           {
                extract($val);
                extract($this->NGramsFreqToStats($decText));
                $statReport = self::statReport($IC, $SD, $IQR);
                echo <<<HTML

                <b>Cipher breaking (Frequency Analysis):</b>

                The cipher text was analyzed using <b>frequency analysis</b>
                with an unknown key length ranging <b>from {$keysLenFromTo[0]} to {$keysLenFromTo[1]}</b> characters.
                <b>Recovered key:</b> $key (length: $keyLen)
                $statReport
                <b>Decrypted text:</b>
                <div>$decText</div>

                HTML;
              $keyD = $key;


              $res = $this -> cribDraggingDecrypter($keyD);
              $decryptedTextArr = [];
              $ICarr = [];
              foreach($res as $iR => $cribDraggingKey)
              {
                    $decryptedTextArr[$iR] = $this -> decrypter($entcryptedText, $cribDraggingKey );
                    $NGramsFreq = $this -> getNGramsFreq($decryptedTextArr[$iR]);
                    extract($this->NGramsFreqToStats($decryptedTextArr[$iR]));
                    $statReport = self::statReport($IC, $SD, $IQR);

                    $ICarr[$iR] = $IC = $this -> getIndexCoincidence($decryptedTextArr[$iR]);
                    $SDarr[$iR] = $SD;
                    $IQRarr[$iR] = $IQR;
                    $statReport = self::statReport($IC, $SD, $IQR);
                    if(debug)
                    {
                        echo <<<HTML
                        $statReport
                        <b>Corrected key:</b> $cribDraggingKey(length: $keyLen)
                        <b>Decrypted text:</b>
                        <div>{$decryptedTextArr[$iR]}</div>
                        HTML;
                    }
              }

              $iR  = array_search(max($SDarr), $SDarr);
              $cribDraggingKey = $res[$iR];
                $IC = $ICarr[$iR];
                $SD = $SDarr[$iR];
                $IQ = $IQRarr[$iR];
              if($cribDraggingKey != $keyD AND $ICarr[$iR] > self::$languageICmin)
              {
                     $statReport = self::statReport($IC, $SD, $IQR,1);
                     echo <<<HTML
                     <b>Key correction (Crib Dragging):</b>

                     The key was corrected using the <b>crib dragging method</b>.
                     <b>Corrected key:</b> $cribDraggingKey(length: $keyLen)
                     $statReport
                     <b>Final decrypted text:</b>
                     <div>{$decryptedTextArr[$iR]}</div>

                     HTML;
              }
           }

       echo "<hr>";
    }

//-----------------------------------------------------

    static function statReport($IC, $SD, $IQR, $final=null){
        $SD = round($SD, 5);
        $IQR = round($IQR, 5);
        $final = ($final)?" final":"";
        return <<<HTML

        <b>Statistical cryptanalysis of$final decrypted text</b>

        <b>Index of coincidence (IC)</b>: $IC
        Analysis of the trigrams frequency
        <b>Standard deviation (SD): </b>$SD
        <b>Interquartile Range (IQR)</b>: $IQR

        HTML;
    }

//-----------------------------------------------------

}

```

### 2. File  index.php

```php
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


?>

```

### 3. File sources.php

```php
    <?PHP
    /*
    Demo PHP-class VigenereCipher
    Version: 1.0, 2026-07-22
    Author: Vladimir Kheifets (vladimir.kheifets.@online.de)
    Copyright (c) 2026 Vladimir Kheifets All Rights Reserved
    */

    $sources = [];

    if($inputTxt = filter_input(INPUT_POST, "inputTxt"))
    {
        $txt = $_POST["txt"];
        if($inputTxt == 2)
            $sources[] = $txt;
        else if($inputTxt == 3)
            $entcryptedText = $txt;
        else
    {

    $sources[] = "The chances of an open-faced marmalade sandwich landing face down on a floor covering are directly correlated to the newness and cost of the carpet.";

    $sources[] = "When I think how I have been swindled by books of Oriental travel, I want a tourist for breakfast. For years and years I have dreamed of the wonders of the Turkish bath; for years and years I have promised myself that I would yet enjoy one.";

    $sources[] = 'enigma message: P1030668  [To] Commanding Admiral of Submarines, Comsubs Training, Comsubs East, SMHS East, 25th Submarine Flotilla, 5th Submarine Flotilla, [and] Port Captain Kiel from Torpedo Recovery Boat 19: At 12[00] hours entered port at Kiel, radio remains manned.';

    $sources[] = "In cryptography, coincidence counting is the technique (invented by William F. Friedman) of putting two texts side-by-side and counting the number of times that identical letters appear in the same position in both texts. This count, either as a ratio of the total or normalized by dividing by the expected count for a random source model, is known as the index of coincidence, or IC or IOC or IoC for short. Because letters in a natural language are not distributed evenly, the IC is higher for such texts than it would be for uniformly random text strings. What makes the IC especially useful is the fact that its value does not change if both texts are scrambled by the same single-alphabet substitution cipher, allowing a cryptanalyst to quickly detect that form of encryption.";

    $sources[] = "The next day, our ship is slow because the wind is not strong. Then the wind is strong again. We go fast. A new storm comes. This storm is bigger. I am scared. The men are scared too. The waves are very big. The waves are like mountains. I see other ships. The ships are like toys. The waves play with the ships. I want to go home again. The storm is very big. The men ask God for help. Then, one man sees a hole in the ship. Water is inside. It is a bad situation. Many men go down. They pump the water out. I am very scared. I can't move. One man comes to me. He says, \"Go down and help.\" So I go down. We pump the water out. I hear a gun. It is a signal from our captain. It is a signal that we have a big problem. Our ship is very broken. Our ship is full of water. We work very hard. We pump the water out. But the hole is very big. More and more water is inside the ship. The weather is better. The waves are smaller. But the ship is full of water. We need help. We see another ship before our ship. The men from the ship send a small boat. The men on the boat go to our ship. The men help us. We go on their boat. Fifteen minutes later, our big ship goes under water. We are safe on the small boat. We see a land. We go to the land.";


    $sources[] = 'Hosepipe bans have been introduced in parts of south-east England as successive heatwaves have left water supplies under strain in parts of the UK. But while spring and early summer have been relatively dry for much of the country - with temperatures regularly exceeding 30C - winter was much wetter than usual. More than eight million households have been placed under hosepipe bans. This means hosepipes cannot be used for watering gardens, washing vehicles and windows and filling pools. It has raised questions about how effectively water resources are being managed, and whether the UK is prepared for drier summers expected with climate change. So how is your area doing and how close are you to a drought?';
    }
    }


    $randomWords = [];
    if($key = filter_input(INPUT_POST, "key"))
    {
        $randomWords[]= strtoupper($key);
    }
    else
    {
        $randomKeys = json_decode(file_get_contents("demoKeys.json"), 1);
        foreach($randomKeys as $row){
            $i = array_rand($row, 1);
            $randomWords[] = $row[$i];
        }
    }

```

### 4. File index.js

```js
    /*
    Demo PHP-class VigenereCipher
    Version: 1.0, 2026-07-22
    Author: Vladimir Kheifets (vladimir.kheifets.@online.de)
    Copyright (c) 2026 Vladimir Kheifets All Rights Reserved
    */

    const show = document.querySelectorAll("input[name='show']")[0];
    const chAll = document.querySelectorAll("input[name='all']")[0];
    const ch = document.querySelectorAll("input[name='extend[]']");
    const ra = document.querySelectorAll("input[name='randomKey']");
    const ke = document.querySelectorAll("input[name='key']")[0];
    const inputTxt = document.querySelectorAll("input[name='inputTxt']");
    const txt = document.querySelectorAll("textarea")[0];
    const txtL = document.querySelectorAll("span")[0];
    const send = document.querySelectorAll("input[name='send']")[0];
    const maxTxtLen = 150;
    //-------------------------------------------

    const setKeyPattern = (set) => {
        if(set)
        {
            pattern = "^([a-zA-Z]){2,12}$";
            ke.setAttribute("pattern", pattern);
            ke.required = true;
        }
        else
        {
            ke.removeAttribute("pattern");
            ke.required =false;
            ke.value = "";
        }

    };

    //-------------------------------------------

    const setTxtAttr = (val) => {
         switch (parseInt(val)) {
                case 1:
                txt.readOnly=true;
                txt.setAttribute("placeholder", "");
                txt.value= "";
                txtL.innerHTML = "";
                ra[0].checked = true;
                send.disabled=false;
                break;

                case 2:
                txt.removeAttribute('readonly')
                txt.value= "";
                txtL.innerHTML = "";
                txt.setAttribute("placeholder", `Input of source text for encryption (at least ${maxTxtLen} characters)`);
                ra[0].checked = true;
                send.disabled = true;
                break;

                case 3:
                txt.value= "";
                txtL.innerHTML = "";
                txt.removeAttribute('readonly');
                txt.setAttribute("placeholder", `Input of encrypted text for decryption (at least ${maxTxtLen} characters)`);
                ra[1].checked = true;
                setKeyPattern();
                send.disabled = true;
                break;
            }
    };

    //-------------------------------------------

    const checKeyOption = (i, val) => {
        i -= 1;
        ra[i].checked = true;
        if(i==2){
            ke.value=val;
            ke.required = true;
        }
        else
        {
            ke.value="";
            ke.required = false;
        }
    };

    //-------------------------------------------

    const checked = (checkAll) => {
        if(parseInt(checkAll))
            chAll.checked = true;

        for (let i = 0; i < ch.length; i++){
            if(val.includes(ch[i].value))
                ch[i].checked = true;
        }
    }

    //-------------------------------------------

    const checkInputTxt = (i,val) => {
       i-=1;
       inputTxt[i].checked=true;
       if(i > 0)
       {
        txt.removeAttribute('readonly');
        txt.value = val;
        let dis = val.length >= maxTxtLen?false:true;
        //send.disabled=dis;
       }
       else
       {
         txt.readOnly=true;
       }
    }

    //-------------------------------------------
    const checkTxtLen = () => {
        let L = txt.value.length;
        let message =  L > 0 ? `Entered ${L} characters` : "";
        txtL.innerHTML = message;
        let dis1 = L >= maxTxtLen?false:true;
        dis2 = true;
        if(!dis1) dis2 = validTxtByIC(txt.value);
        let dis = dis1 || dis2;
        if(dis)
            txtL.setAttribute("class","err");
       else
            txtL.removeAttribute("class");
        send.disabled = dis;
    }

    const getIndexCoincidence = (txt) => {
        txt = txt.replace(/[^a-zA-Z]/g, '').toUpperCase();
        let txtLen = txt.length;
        let L = txtLen * (txtLen - 1);
        if(L == 0) return 0;
        let rankLetters = txt.split("");
        let count = rankLetters.reduce((a,c) => (a[c] = ++a[c] || 1, a) ,{});
        let IC = 0;
        for (const n of Object.values(count))
            IC += (n * (n-1) )/L;
        return IC.toFixed(5);
    }


    const validTxtByIC = (txt)=>{
        let IC = getIndexCoincidence(txt);
        let message;
        if(IC < 0.06 && inputTxt[1].checked){
            message = "Attention!\nYou entered encrypted text instead of the source text.";
            txtL.innerHTML = message;
            return true;
        }
        else  if(IC > 0.06 && inputTxt[2].checked){
            message = "Attention!\nYou entered the source text instead of the encrypted text.";
            txtL.innerHTML =  message;
            return true;
        }
        else
            return false;
    }

    //-------------------------------------------

    ra[2].addEventListener("click", (e)=>{
      setKeyPattern(1);
    });


    [0,1].forEach((i) => {
        ke.value="";
        ke.required = false;
        ra[i].addEventListener("click", (e)=>{
            setKeyPattern();
            if(i == 1)
            {
                inputTxt[2].checked =  true;
                setTxtAttr(3);
            }
            else
            {
                inputTxt[0].checked =  true;
                setTxtAttr(1);
                ke.value="";

            }
        });
    });



    for (var i = 0; i <inputTxt.length; i++) {
        if(inputTxt[i].checked)
            setTxtAttr(i+1);
        inputTxt[i].addEventListener("click", (e)=>{
           setTxtAttr(e.target.value);
        });
    }



    chAll.addEventListener("click", (e)=>{
        let check = e.target.checked;
        for (let i = 0; i < ch.length; i++){
            ch[i].checked = check;
        }
    });

    if(ra[2].checked){
       setKeyPattern(1);
    }

    if(ra[0].checked){
       ke.removeAttribute("pattern");
       ke.required = false;
    }



    txt.addEventListener("input", (e)=>{
        checkTxtLen();
    });

    //--------------------------------------------


```


### 5. File index.css

```css
    /*
    Demo PHP-class VigenereCipher
    Version: 1.0, 2026-07-22
    Author: Vladimir Kheifets (vladimir.kheifets.@online.de)
    Copyright (c) 2026 Vladimir Kheifets All Rights Reserved
    */

    body{
     font-family: arial;
     font-size: 12pt;
     padding: 20pt 0pt 20pt 20pt;
    }
    div{
      width:calk(100%-20px);
      overflow: hidden;
      white-space: pre-wrap;
      padding: 10pt;
      margin-top: 10px;
      border: 1px solid #CCC;
    }
    h1,h2{
      width:calk(100%-20px);
      font-weight: normal;
      font-size: 20px;
      text-align: center;
    }

    h2{
      font-size: 12pt;
    }

    textarea{
      margin-top: 10px;
      width: 150%;
      height: 200px;
      font-size: 12pt;
    }

    textarea:read-only {
      margin-top: 0px;
      width: 150%;
      height: 0px;
      border: 0px;
      resize:none;
    }


    form{
        padding: 0px;
        align-items: center;
        max-width: 300px;
        text-align: left;
        margin-left: auto;
        margin-right: auto;
        white-space: pre;
    }

    input{margin-right: 5px}

    input[type='text']{
        width: 300px;
        font-size: 12pt;
    }

    table{
       border-collapse: collapse;
    }
    td{
        padding:0 0 0 5;
        letter-spacing: 5px;
    }
    .rand{
        font-weight: bold;
        background-color: #E4E4E4;
    }

    .report td{
      padding:2 5 2 5;
      max-width: 80px;
      width: 80px;
      overflow: hidden;
      text-align: center;
      white-space: pre-wrap;
    }

    span{
        position: absolute;
        z-index: 1;
        margin-top:  10px;
        margin-left: 10px;
        height: auto;
        width:100px;
        overflow: hidden;
        white-space: pre-wrap;
    }

    .err{color: red}

```