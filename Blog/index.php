<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Strict//EN">

<html>
	<head>
		<title>Martins Blog</title>
		<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
		<link rel="stylesheet" type="text/css" href="../gaeckler.css">
		<link rel="stylesheet" type="text/css" href="blog.css">
		<link rel="shortcut icon" href="../favicon.ico" type="image/ico">
	</head>
	<body>
		<p>
			<a href="/">Start</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="../badminton.htm">Badminton</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="../Tontechnik/intro.htm">Tontechnik</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="../musik.htm">Musik</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="../Fotografie/foto.htm">Fotografie</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="../Galerie/images.php">Galerie</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="../Software/software.htm">Software</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="index.php">Blog</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="../wordbook.htm">W&ouml;rterbuch</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		</p>
		<hr>
		<h1>Blog</h1>
		<p>
		<?php
			foreach (array_reverse(glob("*.xml")) as $filename) {
				$title = $filename;
				$xml = simplexml_load_file($filename);
				if ($xml) {
					$titel = (string)$xml->headline;
					$teaser = (string)$xml->teaser;
					$datum = (string)$xml->datum;
				}
				if( isset($datum) )
					echo "<b>{$datum}</b> ";
				echo "<a href={$filename}>" . htmlentities($titel, ENT_QUOTES, 'UTF-8') . "</a><br>\n";
				if( isset($teaser) )
					echo htmlentities($teaser, ENT_NOQUOTES, 'UTF-8') . "<br>\n";
			}
		?>
		<hr>
		<P class="copyr">&copy; 1999-2026 by Martin G&auml;ckler <a href="https://www.gaeckler.at/">&Ouml;sterreich</a> <a href="http://www.gäckler.de/">Deutschland</a></P>
	</body>
</html>


<?php
/*
// XML und XSL laden
$xml = new DOMDocument();
$xml->load('nachricht_1.xml');

$xsl = new DOMDocument();
$xsl->load('blog.xsl');

// Prozessor initialisieren
$proc = new XSLTProcessor();
$proc->importStyleSheet($xsl);

// Transformieren und ausgeben
echo $proc->transformToXML($xml);
*/
?>