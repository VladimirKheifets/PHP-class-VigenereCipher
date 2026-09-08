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
		    return 	$resDec;
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

			   if(preg_match(self::$L, $entTextLetter))			   {

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

	public function getNgramsFromWord($word, $wordLen,  &$NGrams, $n=3){
		$word = strtolower($word);
	    $wordLen = $n>1?$wordLen-$n:$wordLen-1;
	    for ($i = 0; $i <= $wordLen; $i++)
	        $NGrams[] = mb_substr($word, $i, $n);
	}
	//-----------------------------------------------------

	public function getNgrams($txt, $n=3){
		$NGrams = [];
		$words = preg_split(self::$nL, strtolower($txt));
		foreach($words as $word){
			$wordLen = mb_strlen($word, 'UTF-8');
			if($wordLen == $n)
				$NGrams[] = $word;
			else if($wordLen > $n)
				$this->getNgramsFromWord($word, $wordLen, $NGrams, $n);
		}
	    return $NGrams;
	}

	//-----------------------------------------------------

	public function setWordsFromNgrams($NGrams) {
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
##############################################################