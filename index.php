<?php
/*
Copyright (c) 2016 Profitbyte AB, Linus Kimselius

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

	define('TEMPORARY_DIRECTORY', '/tmp/');

	// Require our libraries
	require_once('pnum.php');
	require_once('spaab.php');
	
	$error = array();
	
	// Handle for submission
	if ($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		$spaab = new SPAAB();
		
		// Check and validate submission.
		if (isset($_FILES['file']))
		{
			if ($_FILES['file']['size'] == 0)
				$error['file'] = 'Du m&aring;ste v&auml;lja en fil.';
		}
		else
		{
			$error['file'] = 'No file uploaded';
		}
		
		// Transaction code has to be supplied and should consist of a string
		if (isset($_POST['transkod']) && strlen($_POST['transkod']) == 5)
		{
			$transkod = $_POST['transkod'];
		}
		else
			$error['transkod'] = 'F&auml;ltet m&aring;ste fyllas i';
			
		// Income source has to be supplied and should consist of a 2 digit integer
		if (isset($_POST['inkomstkalla']) && strlen($_POST['inkomstkalla']) == 2 && is_numeric($_POST['inkomstkalla']))
		{
			$inkomstkalla = $_POST['inkomstkalla'];
		}
		else
			$error['inkomstkalla'] = 'F&auml;ltet m&aring;ste fyllas i';
		
		// Customer has to be supplied and should consist of numeric
		if (isset($_POST['kundnr']) && is_numeric($_POST['kundnr']))
		{
			$kundnr = $spaab->zero($_POST['kundnr'], 6);
		}
		else
			$error['kundnr'] = 'F&auml;ltet m&aring;ste fyllas i';
			
		// Agreement number has to be supplied and should consist of numeric
		if (isset($_POST['avtalsnr']) && is_numeric($_POST['avtalsnr']))
		{
			$avtalsnr = $spaab->zero($_POST['avtalsnr'], 9);
		}
		else
			$error['avtalsnr'] = 'F&auml;ltet m&aring;ste fyllas i';

		// Year has to be supploed
		if (isset($_POST['year']) && is_numeric($_POST['year']) && strlen($_POST['year']) == 4)
		{
			$year = $_POST['year'];
		}
		else
			$error['year'] = 'F&auml;ltet m&aring;ste fyllas i';
			
		// Month has to be supploed
		if (isset($_POST['month']) && is_numeric($_POST['month']) && strlen($_POST['month']) == 2)
		{
			$month = $_POST['month'];
		}
		else
			$error['month'] = 'F&auml;ltet m&aring;ste fyllas i';
		
		// No errors found. Lets proceed with conversion
		if (!count($error))
		{
			// At the moment we only support monthly reporting...
			$period = "01";
			
			$uploadfile = TEMPORARY_DIRECTORY . 'spaab_conversion_' . time() . '.txt'; //basename($_FILES['file']['name']);
			
			// Open and read file contents to string
			if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
				$str = file_get_contents($uploadfile);

				// Proceed with conversion
				$result = $spaab->convert(
					$str, 
					$kundnr,
					$transkod,
					$inkomstkalla,
					$avtalsnr,
					$year, 
					$month, 
					$period
				);
				
				unlink($uploadfile);
			}
			else
				die('Sorry. File upload failed');			
			
			if ($result === false)
				die('Sorry, something went wrong.');

			// Calculate the file size
			$size   = mb_strlen($result);
			
			// Set headers for file output to browser
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . 'export' . time() . '.txt'); 
			header('Content-Transfer-Encoding: binary');
			header('Connection: Keep-Alive');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . $size);

			// Okey, let's send the results!
			echo $result;
			
			// Exit now
			die();
		}
	}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="utf-8">
  <title>SPCS L&ouml;n till Pensionsvalet - Konverterare</title>
  <meta name="description" content="Konvertera filer i SPCS l&ouml; till pensionsvalet.">
  <meta name="author" content="Linus Kimselius">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="//fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/skeleton.css">
  <link rel="stylesheet" href="css/main.css">
</head>
<body>
	<div class="container holder">
		<h1>Konvertera fr&aring;n SPCS l&ouml;n till pensionsvalet</h1>
		<p>
			Det h&auml;r scriptet konverterar filer fr&aring;n SPCS l&ouml;n till ett filformat som passar f&ouml;r uppladdning i pensionsvalet.
		</p>
		<form enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
			<input type="hidden" name="MAX_FILE_SIZE" value="30000" />
			
			<label for="file">Ladda upp fil</label>
			<?php if (isset($error['file'])) { ?><p class="error"><?php echo $error['file']; ?></p><?php } ?>
			<input type="file" name="file" id="file" class="u-full-width">

			<label for="kundnr">Kundnummer</label>
			<?php if (isset($error['kundnr'])) { ?><p class="error"><?php echo $error['kundnr']; ?></p><?php } ?>
			<input type="text" name="kundnr" id="kundnr" class="u-full-width" placeholder="Ange ert kundnummer hos pensionvalet" value="<?php if (isset($_POST['kundnr'])) echo $_POST['kundnr']; ?>">
		
			<label for="avtalsnr">Avtalsnummer</label>
			<?php if (isset($error['avtalsnr'])) { ?><p class="error"><?php echo $error['avtalsnr']; ?></p><?php } ?>
			<input type="text" name="avtalsnr" id="avtalsnr" class="u-full-width" placeholder="Ange ert avtalsnummer hos pensionvalet" value="<?php if (isset($_POST['avtalsnr'])) echo $_POST['avtalsnr']; ?>">
		
			<label for="transkod">Transaktionskod</label>
			<?php if (isset($error['transkod'])) { ?><p class="error"><?php echo $error['transkod']; ?></p><?php } ?>
			<input type="text" name="transkod" id="transkod" class="u-full-width" placeholder="Fem tecken (exempelvis E0203)" value="<?php if (isset($_POST['transkod'])) echo $_POST['transkod']; else echo "E0203"; ?>">

			<label for="inkomstkalla">Inkomstk&auml;lla</label>
			<?php if (isset($error['inkomstkalla'])) { ?><p class="error"><?php echo $error['inkomstkalla']; ?></p><?php } ?>
			<select name="inkomstkalla" id="inkomstkalla" class="u-full-width">
				<option value="01">F&ouml;retag</option>
			</select>
			
			<label for="year">&Aring;r</label>
			<?php if (isset($error['year'])) { ?><p class="error"><?php echo $error['year']; ?></p><?php } ?>
			<select name="year" id="year" class="u-full-width">
				<?php for ($i=date("Y")-3;$i<date("Y")+3;$i++) { ?>
				<option value="<?php echo $i; ?>" <?php if ($i == date("Y")) echo "selected"; ?>><?php echo $i; ?></option>
				<?php } ?>
			</select>
			
			<label for="month">M&aring;nad</label>
			<?php if (isset($error['manad'])) { ?><p class="error"><?php echo $error['manad']; ?></p><?php } ?>
			<select name="month" id="month" class="u-full-width">
				<option value="01">Januari</option>
				<option value="02">Februari</option>
				<option value="03">Mars</option>
				<option value="04">April</option>
				<option value="05">Maj</option>
				<option value="06">Juni</option>
				<option value="07">Juli</option>
				<option value="08">Augusti</option>
				<option value="09">September</option>
				<option value="10">Oktober</option>
				<option value="11">November</option>
				<option value="12">December</option>
			</select>
						
			<input class="button-primary" value="Skicka" type="submit">
		</form>
	</div>
</body>
</html>
