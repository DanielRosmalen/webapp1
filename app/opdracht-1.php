<!DOCTYPE html>
<html lang="en">
<head>
    <title>Mijn Profiel</title>
</head>
<body>

<h1>Mijn Profiel</h1>
<?php

// STAP 1: Maak hier je variabelen.
$naam = "Daniel";
$leeftijd = 17;
$uurloon = 100;
$urenGewerkt = 50;

// STAP 2: Toon hier je naam en leeftijd.
echo "<div>";
echo $naam;
echo "</div>";
echo "<div>";
echo $leeftijd;
echo "</div>";

// STAP 3: Bereken hier hoeveel je hebt verdiend en laat dit zien.
echo "<div>";
echo    $uurloon * $urenGewerkt;
echo "</div>";
?>
</body>
</html>