<?php

require_once('dmaprint.class.php');

$printers = array("kiosk1hamon", "kiosk1flora", "kiosk2flora", "kiosk3flora", "jprinter", "friendshamon", "friendsflora");

if ($_POST && isset($_POST['printtype']))
{
	switch($_POST['printtype'])
	{
		case "idcard":
			$idcard = new dmaprintid($_POST['name'], $_POST['number'], $_POST['printer']);
			$idcard->doPrint();
			break;
		case "coupon":
			$coupon = new dmaprintcoupon($_POST['text'], $_POST['expires'], $_POST['number'], $_POST['printer']);
			$coupon->doPrint();
			break;
	}

	die("printed");
}

?>
<html>
	<head>
		<title>Printing POC</title>
    <style type="text/css">
    .n {
	font-size: 14pt;
}
    </style>
	</head>
	<body>
		<h2>ID Card</h2>

		<form method="post">
			<input type="hidden" name="printtype" value="idcard" />
		
			<label for="name" class="n">User Name</label>
			<input type="text" name="name"class="n"></input><br/>

			<label for="number"class="n">User ID Number</label>
			<input type="text" name="number"></input><br/>

			<label for="printer"class="n">Printer</label>
			<select name="printer">
				<option></option>
<?php
	foreach($printers as $p) {
		print("<option value='".$p."'>".$p."</option>");
	}
?>
			</select><br/>
			
			<button type="submit">Print ID Card</button>
		</form>


		<h2>Coupon</h2>

		<form method="post">
			<input type="hidden" name="printtype" value="coupon" />

			<label for="text" class="n">Coupon Text</label>
			<textarea name="text"></textarea><br/>

			<label for="expires"class="n">Expiration Date</label>
			<input type="text" name="expires"></input><br/>

			<label for="code"class="n">Coupon Code</label>
			<input type="text" name="number"></input><br/>
			
			<label for="printer"class="n">Printer</label>
			<select name="printer">
				<option></option>
<?php
        foreach($printers as $p) {
                print("<option value='".$p."'>".$p."</option>");
        }
?>

			</select><br/>
			
			<button type="submit">Print Coupon</button>
		</form>

		</body>
</html>
