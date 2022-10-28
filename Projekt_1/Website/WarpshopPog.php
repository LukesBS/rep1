<?php
define("USER", "root");
define("PASS", "usbw");
define("PRICE_FROM_KEY", "PreisVon");
define("PRICE_TO_KEY", "PreisBis");
define("START_FROM_KEY", "startVon");
define("DATASET_SIZE", 10);

function getPriceFrom() {
  return isset($_POST[PRICE_FROM_KEY]) ? $_POST[PRICE_FROM_KEY] : (isset($_GET[PRICE_FROM_KEY]) ? $_GET[PRICE_FROM_KEY] : "");
}

function getPriceTo() {
  return isset($_POST[PRICE_TO_KEY]) ? $_POST[PRICE_TO_KEY] : (isset($_GET[PRICE_TO_KEY]) ? $_GET[PRICE_TO_KEY] : "");
}

function getSelectBody() {
  $priceFrom = getPriceFrom();
  $priceTo = getPriceTo();

  if ($priceFrom == "" || $priceTo == "") {
    return "";
  }

  return "FROM product pd " 
    ."JOIN prodpricefromtomat pr ON pr.ProductID = pd.ID "
    ."WHERE pr.fromDate <= DATE(NOW()) "
    ."AND pr.toDate > DATE(NOW()) "
    ."AND pr.Price >= $priceFrom "
    ."AND pr.Price <= $priceTo ";
}

function getLinkData() {
  $linkData = array();

  $selectBody = getSelectBody();
  $noOfDataSets = 0;
	
  if ($selectBody == "") {
    $linkData;
  }
	
  try {
    $dbh = new PDO('mysql:host=localhost;dbname=warpshop', USER, PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));	
    $sth = $dbh->query("SELECT COUNT(*) AS numSets " .$selectBody);
				
    if ($sth !== false) {
      foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $noOfDataSets = intval($row["numSets"]);
      }
    } else {
      return $linkData;
    }
  } catch (Exception $e) {
	  echo $e->getMessage();
  }	

  $linkNo = 0;
  for($i = 0; $i < $noOfDataSets; $i+= DATASET_SIZE) {
    $linkData[$linkNo++] = $i;
  }		
  $sth = null;
  $dbh = null;
	
  return $linkData;
}

function getTableContent() {
  $selectData = array();
  $startValue = isset($_GET[START_FROM_KEY]) ? $_GET[START_FROM_KEY] : 0;
  $outputSize = DATASET_SIZE;
  $selectBody = getSelectBody();
	
  if ($selectBody == "") {
    return $selectData;
  }
	
  try {
    $dbh = new PDO('mysql:host=localhost;dbname=warpshop', USER, PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

    $sqlStatement = "SELECT pd.ModelNumber, pd.Name, pr.Price "
                    .$selectBody ."LIMIT $startValue, $outputSize";

    $sth = $dbh->query($sqlStatement);
    if ($sth !== false) {
      $noData = 0;
      foreach ($sth->fetchAll(PDO::FETCH_NUM) as $row) {
        $selectData[$noData++] = $row;
      }
    } else {
      return $selectData;
    }
  } catch (Exception $e) {
	  	  echo $e->getMessage();
  }	

  $sth = null;
  $dbh = null;
  return $selectData;
}

function showTableData() {
  $tableData = getTableContent();
  foreach($tableData as $row) {
	echo "<tr>";		
	foreach($row as $value) {
	  echo "<td>" .$value ."</td>";
	}			
	echo "</tr>";		
  }
}

function showLinkData() {
  $handlingFileName = $_SERVER['SCRIPT_NAME'];
  $priceFrom = getPriceFrom();
  $priceTo = getPriceTo();
  $linkData = getLinkData();
  for($i = 0; $i < count($linkData); $i++) {
    echo "<a href=$handlingFileName?" .PRICE_FROM_KEY ."=$priceFrom&" .PRICE_TO_KEY ."=$priceTo&" .START_FROM_KEY ."=" .$linkData[$i] .">". ($i+1) ."</a> ";
  }
}

?>

<!DOCTYPE html>
<html lang="de">
 <head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="css/OrderData.css">
  <title>warshop proof of concept</title>
 </head>
 <body>
	<form action="WarpshopPog.php" method="post">
		Preis von: <input type="text" name="<?php echo PRICE_FROM_KEY; ?>"/><br/>
		Preis bis: <input type="text" name="<?php echo PRICE_TO_KEY; ?>"/><br/>
		<input type="submit" value="send"/>
	</form>
	<br/>
	<table id="myTab"> 
	  <tbody id="myTabBody"> 
	    <tr class="myHd"> 
		  <th class="myHd">ID:</th> 
		  <th class="myHd">Produkt:</th> 
		  <th class="myHd">Preis:</th>
		</tr>
		<?php showTableData(); ?>
	  </tbody>
	</table><br/>
	<?php showLinkData(); ?>
  </body>
</html>
