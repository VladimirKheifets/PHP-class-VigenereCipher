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
$sources[] = "When you are stuck in traffic and switch to a different lane, your old lane suddenly starts moving faster than the new one.";

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


