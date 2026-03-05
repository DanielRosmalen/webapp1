<?php
// Studentenlijst
$studenten = [
        ["naam" => "Emma", "leeftijd" => 17],
        ["naam" => "Liam", "leeftijd" => 19],
        ["naam" => "Noah", "leeftijd" => 16],
        ["naam" => "Karim", "leeftijd" => 21],
        ["naam" => "Lucas", "leeftijd" => 18]
];

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Studentenlijst</title>
</head>
<body>

<h1>Studentenlijst</h1>

<!-- HIER MOET JOUW CODE KOMEN -->
<!-- Toon de eerste, derde en vijfde student uit de lijst -->
<?php
echo "<div>". $studenten[0]["naam"] . " " . $studenten[0]["leeftijd"] . "</div>";
echo "<div>". $studenten[2]["naam"] . " " . $studenten[2]["leeftijd"] . "</div>";
echo "<div>". $studenten[4]["naam"] . " " . $studenten[4]["leeftijd"] . "</div>";
?>

</body>
</html>