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
<!-- Toon alle studenten. Studenten die minderjarig zijn markeer je rood. -->
<!-- Studenten die volwassen zijn, markeer je groen -->
<?php
    foreach ($studenten as $student) {
        if ($student["leeftijd"] < 18) {
            echo "<div style='color: red;'>" . $student["naam"] . " " . $student["leeftijd"] . "</div>";
        }
        else {
            echo "<div style='color: green;'>" . $student["naam"] . " " . $student["leeftijd"] . "</div>";
        }
    }

?>
</body>
</html>