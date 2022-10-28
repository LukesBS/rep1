<?php

function readDb($postData) {

    if (isset($postData["minweight"])) {

        $dbh = new PDO('mysql:host=localhost:3306;dbname=warpshop','root', 'usbw');
        $stmt = $dbh->prepare("SELECT Name, ModelNumber, gewicht, groesse FROM Product WHERE gewicht >= ? ORDER BY gewicht LIMIT 10");

        if ($stmt->execute(array($postData["minweight"]))) {

            while ($row = $stmt->fetch()) {
                echo $row["Name"] ."|" .$row["ModelNumber"]."|" .$row["gewicht"]."|" .$row["groesse"] ."<br/>";
            }

        }

    $stmt = null;
    $dbh = null;

    }
} 
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PHP und HTML</title>
</head>
<body>
    <form action="SeiteB.php" method="post">
        Minimalgewicht: 
        <input type="text" name="minweight">
        <input type="submit" value="send">
    </form>
    <br/>
    <?php readDb($_POST); ?>
</body>
</html>