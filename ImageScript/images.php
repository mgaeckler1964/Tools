<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Strict//EN">

<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
		<link rel="stylesheet" type="text/css" href="images.css">
		<?php
			/*
				==================
				here it begins 8-)
				==================
			*/
			$displayComment = true;
			$displayDescription = true;
			$displayCopyrightInfo = true;
			$displayImageData = true;
			$displayPreviewFilename = true;
			$displayArtist = true;
			$displayCameraSettings = true;
			
			$previewsPerLine = 2;
			$imagesPerLine = 2;
			$iePreviewsPerLine = 2;
			$ieImagesPerLine = 2;
			$thumbWidth = 0;
			$thumbCache = 0;
			$imagePosition = 0;
			$imageWidth = 0;
			$imageHeight = 0;
			
			$imageLeft = null;

			
			$sep = false;
			$dir = array_key_exists("DIR",$_GET) ? $_GET["DIR"] : "";
			if( $dir == "" )
				$dir=".";
	
			$title = substr( $dir, 2 );
			if( $title == "" )
				$title = "Galerie";
				
			/*
				check for stupid browser that can not display the picture frame
			*/
			$goodBrowser = true;
			$browser = strtoupper( $_SERVER['HTTP_USER_AGENT'] );
			$msie = strpos( $browser, "MSIE" );
			if( $msie )
			{
				$colon = strpos( $browser, ";", $msie );
				if( $colon )
				{
					$msieVersion = trim( substr( $browser, $msie+4, $colon-$msie - 4 ) );
					if( $msieVersion < 7 )
						$goodBrowser = false;
				}
			}

			/*
				load the header if available within the root directory and the actual gallery
			*/

			$pathList = explode( '/', $dir );
			$tmpDir = "";
			foreach( $pathList as $subDir )
			{
				$tmpDir .= $subDir .'/';
				
				$pathName = $tmpDir . "imagehead.php";
				if( is_file( $pathName ) )
					include_once( $pathName );
			}

			$imageHtmlSize = "";
			if( $imageWidth )
				$imageHtmlSize .= "width = '$imageWidth' ";
			if( $imageHeight )
				$imageHtmlSize .= "height = '$imageHeight' ";

			// set the title
			echo "<title>$title</title>\n";
			
			/*
				================================================
				some useful function.
				Note: Unicode detection is not very reliable 8-(
				================================================
			*/
			
			function convertUnicode( $comment )
			{
				$newComment = "";
				for( $i=0; $i<strlen( $comment ); $i += 2 )
				{
					$newComment = $newComment . $comment[$i];
				}
				
				return $newComment;
			}
			function makeParamString( $dirname )
			{
				return str_replace( ' ', '+', str_replace( '&', '%26', $dirname ) );
			}
			function displayExifInfo( $imagePath, $imageTitle, $width, $height )
			{
				global	$displayComment, $displayDescription, $displayArtist, $displayCopyrightInfo, $displayImageData,
						$displayCameraSettings, $displayPreviewFilename;

				if( $displayComment || $displayArtist || $displayCopyrightInfo || $displayImageData || $displayCameraSettings )
				{
					if( function_exists( "exif_read_data" ) )
						$exif = @exif_read_data($imagePath, 0, true );
					if( $exif )
					{
						if( $displayComment && isset($exif['COMPUTED']['UserComment']) )
						{
							$comment = trim( $exif['COMPUTED']['UserComment'] );
							if( $comment && strlen( $comment ) > 0 && $comment != '-'  )
								echo "<span class='UserComment'><span class='UserCommentLabel'>Titel: </span>$comment</span>\n";
						}
						
						if( $displayDescription && isset($exif['IFD0']['ImageDescription']) )
						{
							$comment = trim( $exif['IFD0']['ImageDescription'] );
							if( $comment && strlen( $comment ) > 0 && $comment != '-' )
								echo "<span class='ImageDescription'><span class='ImageDescriptionLabel'>Beschreibung: </span>$comment</span>\n";
						}
	
						if( $displayArtist && isset($exif['IFD0']['Artist']) )
						{
							$comment = trim( $exif['IFD0']['Artist'] );
							if( $comment && strlen( $comment ) > 0 && $comment != '-'  )
								echo "<span class='ImageArtist'><span class='ImageArtistLabel'>Fotograf: </span>$comment</span>\n";
						}
	
						if( $displayCopyrightInfo && isset($exif['COMPUTED']['Copyright']) )
						{
							$comment = trim( $exif['COMPUTED']['Copyright'] );
							if( $comment && strlen( $comment ) > 0 && $comment != '-'  )
								echo "<span class='copyRightInfo'>$comment</span>\n";
						}
						if( $displayImageData )
						{
							$imageData = "";
							if( isset($exif['EXIF']['DateTimeOriginal']) )
							{
								$comment = trim( $exif['EXIF']['DateTimeOriginal'] );
								if( $comment && strlen( $comment ) > 0 && $comment != '-'  )
									$imageData .= "<span class='ImageDateLabel'>Datum/Zeit: </span>$comment\n";
							}
								
							if( $width && $height )
								$imageData .= "<span class='ImageSizeLabel'>Gr&ouml;&szlig;e: </span>$width * $height\n";

							if( strlen( $imageData ) )
								echo "<span class='ImageData'>$imageData</span>\n";
						}
						if( $displayCameraSettings )
						{
							$cameraSettings = "";
							if( isset($exif['COMPUTED']['ApertureFNumber']) )
							{
								$comment = trim( $exif['COMPUTED']['ApertureFNumber'] );
								if( $comment && strlen( $comment ) > 0 )
									$cameraSettings .= "<span class='cameraSettingsLabel'>Blende:&nbsp;</span>$comment\n";
							}
							if( isset($exif['EXIF']['ExposureTime']) )
							{
								$comment = trim( $exif['EXIF']['ExposureTime'] );
								if( $comment && strlen( $comment ) > 0 )
								{
									eval( "\$comment=$comment;" );
									if( $comment < 1 )
										$comment = "1/" . 1/$comment;
									$cameraSettings .= "<span class='cameraSettingsLabel'>Zeit:&nbsp;</span>$comment s\n";
								}
							}
							if( isset($exif['EXIF']['ISOSpeedRatings']) )
							{
								$comment = trim( $exif['EXIF']['ISOSpeedRatings'] );
								if( $comment && strlen( $comment ) > 0 )
								{
									eval( "\$comment=$comment;" );
									$cameraSettings .= "<span class='cameraSettingsLabel'>ISO:&nbsp;</span>$comment\n";
								}
							}
							if( isset($exif['EXIF']['FocalLength']) )
							{
								$comment = trim( $exif['EXIF']['FocalLength'] );
								if( $comment && strlen( $comment ) > 0 )
								{
									eval( "\$comment=$comment;" );
									$cameraSettings .= "<span class='cameraSettingsLabel'>Brennweite:&nbsp;</span>$comment mm\n";
								}
							}
							if( isset($exif['IFD0']['Model']) )
							{
								$comment = trim( $exif['IFD0']['Model'] );
								if( $comment && strlen( $comment ) > 0 )
									$cameraSettings .= "<span class='cameraSettingsLabel'>Kamera:&nbsp;</span>$comment\n";
	
							}
							if( isset($exif['IFD0']['LensModel']) )
							{
								$comment = trim( $exif['IFD0']['LensModel'] );
								if( $comment && strlen( $comment ) > 0 )
									$cameraSettings .= "<span class='cameraSettingsLabel'>Objektiv:&nbsp;</span>$comment\n";
	
							}
							if( strlen( $cameraSettings ) )
								echo "<span class='cameraSettings'>$cameraSettings</span>\n";
						}
	
						/*
							foreach ($exif as $key => $section) 
							{
								foreach ($section as $name => $val) 
								{
									echo "$key.$name: $val<br />\n";
								}
							}
						*/
					}
				}
				if( $displayPreviewFilename )
					echo "<span class='imageFileName'>$imageTitle</span>\n";
			}
			function getThumbTag( $file, $filename, $thumbWidth, $imageClass )
			{
				global $thumbCache;
				
				$htmlSize = "width='$thumbWidth'";

				$imageUrl = "imagethumb.php?IMAGE=" . makeParamString( $filename ) . "&WIDTH=$thumbWidth&CACHE=$thumbCache";

				$slashPos = strrpos( $filename, '/' );
				if( $slashPos )
					$cacheFile = substr( $filename, 0, $slashPos ) . "/thumbs" . substr( $filename, $slashPos );
				
				if( is_file( $cacheFile ) )
				{
				    list($width, $height, $image_type, $htmlSize ) = getimagesize( $cacheFile );
					if( $width == $thumbWidth )
						$imageUrl = $cacheFile;
			
				}
				
				return "<img class='$imageClass' src='$imageUrl' title='$file' alt='$file' $htmlSize>";
			}
			function readDirectory( $dir )
			{
				$files = null;
				$cfgFile = $dir . "/.files.cfg";

				if( is_file( $cfgFile ) )
				{
					$handle = fopen ( $cfgFile, "r");
					while (!feof($handle))
					{
						$file = fgets($handle);
						$file = trim( $file );
						if( $file > "" )
						{
							if( substr( $file, -1 ) === "\\" )
								$file = substr( $file, 0, -1 );
							$files[] = $file;
						}
					}
					fclose ($handle);
				}
				else
				{
					if( $dh=opendir($dir) )
					{
						while(( $file=readdir($dh)) !== false )
							$files[] = $file;
					
						closedir( $dh );
						if( count( $files ) > 0 )
							natcasesort( $files );
					}
				}

				return $files;
			}
					
		?>
	</head>
	<body>
		<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
			<?php
				$icons[1] = "index.jpg";
				$icons[2] = "imagempty.gif";
				
				if( isset($imagePrev) && $imagePrev )
				{
					$icons[3] = $imagePrev;
					echo "var imagePrev = \"$imagePrev\";\n";
				}
				else
					echo "var imagePrev = null;\n";

				if( isset($imageNext) && $imageNext )
				{
					$icons[] = $imageNext;
					echo "var imageNext = \"$imageNext\";\n";
				}
				else
					echo "var imageNext = null;\n";

				if( isset($imageSmaller) && $imageSmaller )
				{
					$icons[] = $imageSmaller;
					echo "var imageSmaller = \"$imageSmaller\";\n";
				}
				else
					echo "var imageSmaller = null;\n";

				if( isset($imageScreen) && $imageScreen )
				{
					$icons[] = $imageScreen;
					echo "var imageScreen = \"$imageScreen\";\n";
				}
				else
					echo "var imageScreen = null;\n";

				if( isset($image100) && $image100 )
				{
					$icons[] = $image100;
					echo "var image100 = \"$image100\";\n";
				}
				else
					echo "var image100 = null;\n";

				if( isset($imageBigger) && $imageBigger )
				{
					$icons[] = $imageBigger;
					echo "var imageBigger = \"$imageBigger\";\n";
				}
				else
					echo "var imageBigger = null;\n";

				if( isset($imageClose) && $imageClose )
				{
					$icons[] = $imageClose;
					echo "var imageClose = \"$imageClose\";\n";
				}
				else
					echo "var imageClose = null;\n";
			?>
		</SCRIPT>
		<SCRIPT LANGUAGE="JavaScript" SRC="images.js" TYPE="text/javascript">
		</SCRIPT>
		<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
			<?php
				if( isset($destMargin) && $destMargin )
					echo "destMargin = $destMargin;\n";
				if( isset($smalMargin) && $smalMargin )
					echo "smalMargin = $smalMargin;\n";
				if( isset($growSpeed) && $growSpeed )
					echo "growSpeed = $growSpeed;\n";
				if( isset($growIncrement) && $growIncrement )
					echo "growIncrement = $growIncrement;\n";
				if( isset($imageWidth) && $imageWidth )
					echo "imageWidth = \"$imageWidth\";\n";
				if( isset($imageHeight) && $imageHeight )
					echo "imageHeight = \"$imageHeight\";\n";
			?>
		</SCRIPT>
		<?php
			// now dislplay the title again
			echo "<div class='GalleryTitle'>";
				if( is_file( "menu_bar.html" ) )
					include( "menu_bar.html" );
				echo "<h1>$title</h1>".
			"</div>\n";
			echo "<div class='GalleryContent'>\n";

			// warn user, that we have problems with his browser
			if( !$goodBrowser )
			{
				echo "<p class='BrowserWarning'>Bitte aktualisieren Sie ihren Browser. Gefunden wurde $browser</p>";
				$previewsPerLine = $iePreviewsPerLine;
				$imagesPerLine = $ieImagesPerLine;
			}
			
			/*
				======================================================================================
				check the directory for illegal values
				======================================================================================
			*/
			if( $dir != "." )
			{
				if( substr( $dir, 0, 2 ) != "./" || strpos( $dir, "/." ) > 0 )
				{
					echo "<p>Ung&uuml;ltiges Verzeichnis $dir</p>\n";
					echo "<p><a href='images.php'>Zum Start</a></p>\n";
					exit( -1 );
				}

				$updir = strrpos( $dir, '/' );
				$updir = makeParamString( substr( $dir, 0, $updir ) );
				echo "<p class='upLink'><a class='upLink' href='images.php?DIR=$updir'>nach oben</a></p><hr class='upLink'>\n";
			}
			
			/*
				======================================================================================
				create the custom view part
				======================================================================================
			*/
			$customview = $dir . "/customview.html";
			if( is_file( $customview ) )
			{
				echo "<div class='customView'>";
				include( $customview );
				echo "<hr class='customView'>\n";
				echo "</div>\n";	//  class='customView'
			}
	

			if( is_file( $dir . "/left.html" ) || is_file( $dir . "/right.html" ) || $imagePosition )
			{
				echo "<table class='colLayout'><tr class='colLayout'>\n";
				if( $imagePosition == 1 )
					echo "<td class='inlineImage'><img id='inlineImage' class='inlineImage' $imageHtmlSize></td>\n";

				if( is_file( $dir . "/left.html" ) )
				{
					echo "<td class='leftCol'>";
					include($dir . "/left.html");
					echo "</td>\n";
				}
				if( $imagePosition == 2 )
					echo "<td class='inlineImage'><img id='inlineImage' class='inlineImage' $imageHtmlSize></td>\n";
				echo "<td class='galleryCol'>";
			}

			/*
				======================================================================================
				read directory content
				======================================================================================
			*/
			$files = readDirectory( $dir );
	
			/*
				======================================================================================
				display subdirectories
				======================================================================================
			*/
			if( count( $files ) > 0 )
			{
				if( !$previewsPerLine )
					$cellTag = "span";
				else
					$cellTag = "td";

				$i = 0;
				foreach( $files as $file )
				{
					$dirname = $dir . "/" . $file;
					
					if( is_dir( $dirname ) && $file != "." && $file != ".." && $file != "large" && $file != "preview" && $file != "thumbs" )
					{
						if( !$i )
						{
							if( $previewsPerLine )
								echo "<table class='galleryPreview'><tr class='galleryPreview'>";
							else
								echo "<div class='galleryPreview'>";
						}
						
						$galeryPreview = $dirname . "/" . "index.jpg";
						if( is_file($galeryPreview) )
							$size = getimagesize( $galeryPreview );
						else
							unset( $size );
						if( isset( $size ) )
						{
							$width = $size[0];
							$height = $size[1];
							if( $width > $height )
								$subclass = "quer";
							else if( $width < $height )
								$subclass = "hoch";
							else
								$subclass = "quadrat";
							$htmlSize = $size[3];
						}
						else
							$subclass = "leer";

						$dirUrl = makeParamString( $dirname );
						
						echo "<$cellTag class='galleryPreview $subclass'><a href='images.php?DIR=$dirUrl'>";
						if( isset( $size ) )
						{
							echo "<img class='galleryPreview $subclass' src='$galeryPreview' title='$file' alt='$file' $htmlSize>";
						}
						echo "<br>$file</a>";

						$customview = $dirname . "/description.html";
						if( is_file( $customview ) )
							include( $customview );
						echo "</$cellTag>\n";
	
						$i++;
						if( $previewsPerLine && ($i % $previewsPerLine == 0) )
							echo "</tr>\n<tr>";
					}
				}
				if( $i )
				{
					if( $previewsPerLine )
						echo "</tr></table> <!-- class='galleryPreview' -->\n";
					else
						echo "</div><br clear='left'> <!-- class='galleryPreview' -->\n";
					$sep = true;
				}
				else
					$sep = false;
				
				/*
					======================================================================================
					display single images (and generate thumbs if required)
					======================================================================================
				*/
				$i = 0;
				if( !$imagesPerLine )
					$cellTag = "span";
				else
					$cellTag = "td";

				foreach( $files as $file )
				{
					$filename = $dir . "/" . $file;
					if( !is_dir( $filename ) && !array_search( $file, $icons ) && is_readable($filename) )
					{
						$size = getimagesize( $filename );
						if( $size && ($size["mime"] == "image/jpeg" || $size["mime"] == "image/gif" || $size["mime"] == "image/png") )
						{
							if( !$i )
							{
								if( $sep )	// did we display a gallery preview?
									echo "<hr class='galleryPreview' style='clear:left;'>\n";

								if( $imagesPerLine )
									echo "<table class='imageTable'><tr class='imageTable'>";
								else
									echo "<div class='imageTable'>";
							}

							$width = $size[0];
							$height = $size[1];
							if( $width > $height )
								$subclass = "quer";
							else if( $width < $height )
								$subclass = "hoch";
							else
								$subclass = "quadrat";
								
							echo "<$cellTag class='imageTable $subclass'>";

							if( $thumbWidth )
							{
								$newWidth = $thumbWidth;
								if( $width < $height )	// is portraet photo
								{
									$newWidth = (int)($width / ($height/$thumbWidth));
								}
								
								if( $imagePosition )
								{
									echo "<a href='javascript:showInlinePic( \"$destImage\" );'><img class='imagePreview $subclass' src='$filename' title='$file' alt='$file' $htmlSize></a>";
								}
								else if( $goodBrowser )
								{
									echo "<a href='javascript:showPic( \"$filename\", $size[0], $size[1]);'>" . getThumbTag( $file, $filename, $newWidth, "imageTable " . $subclass ) . "</a>\n";
									echo "<script type='text/javascript'>addGalleryImage( \"$filename\", $size[0], $size[1]);</script>\n";
								}
								else
									echo "<a href='$filename' target='_blank'>" . getThumbTag( $file, $filename, $newWidth, "imageTable " . $subclass ) . "</a>\n";

								/*
									remove no longer required thumbs
								*/
								if( $thumbCache )
								{
									$thumbDir = $dir . "/thumbs";
									if( $dh=opendir($thumbDir) )
									{
										while(( $thumbFile=readdir($dh)) !== false )
										{
											if( $thumbFile != "." && $thumbFile != ".." && !is_file( $dir ."/" . $thumbFile ) )
												unlink( $thumbDir ."/" . $thumbFile );
										}
				
										closedir( $dh );
									}
								}

							}
							else
							{
								$htmlSize = $size[3];
								echo "<img src='$filename' title='$file' alt='$file' $htmlSize>";
							}

							displayExifInfo( $filename, $file, $width, $height );
							
							echo "</$cellTag>\n";
							$i++;
							if( $imagesPerLine && ($i % $imagesPerLine == 0) )
								echo "</tr>\n<tr class='imageTable'>";
						}
					}
				}
				if( $i )
				{
					if( $imagesPerLine )
						echo "</tr></table> <!-- class='imageTable' -->\n";
					else
						echo "</div><br clear='left'> <!-- class='imageTable' -->\n";
				}
			}
			
			/*
				read preview directory
			*/
			$previewDir = $dir . "/preview";
			$largeDir = $dir . "/large";
			$files = is_dir( $previewDir ) ? readDirectory( $previewDir ) : array();

			/*
				======================================================================================
				display previews from "preview" subdirectory with link to "large" subdirectory
				======================================================================================
			*/
			if( count( $files ) > 0 )
			{
				$i = 0;
				if( !$imagesPerLine )
					$cellTag = "span";
				else
					$cellTag = "td";
				foreach( $files as $file )
				{
					$filename = $previewDir . "/" . $file;
					$destImage = $largeDir . "/" . $file;
					if( !is_dir( $filename ) )
					{
						$size = getimagesize( $filename );
						if( $size )
						{
							$width = $size[0];
							$height = $size[1];
							if( $width > $height )
								$subclass = "quer";
							else if( $width < $height )
								$subclass = "hoch";
							else
								$subclass = "quadrat";

							$htmlSize = $size[3];

							if( !$i )
							{
								if( $imagesPerLine )
									echo "<table class='imagePreview'><tr class='imagePreview'>";
								else
									echo "<div class='imagePreview'>";
							}

							$size = getimagesize( $destImage );
							echo "<$cellTag class='imagePreview $subclass'>";
							if( $size )
							{
								$width = $size[0];
								$height = $size[1];

								if( $imagePosition )
								{
									echo "<a href='javascript:showInlinePic( \"$destImage\" );'><img class='imagePreview $subclass' src='$filename' title='$file' alt='$file' $htmlSize></a>";
								}
								else if( $goodBrowser )
								{
									echo "<a href='javascript:showPic( \"$destImage\", $width, $height);'><img class='imagePreview $subclass' src='$filename' title='$file' alt='$file' $htmlSize></a>";
									echo "<script type='text/javascript'>addGalleryImage( \"$destImage\", $width, $height);</script>\n";
								}
								else
									echo "<a href='$destImage' target='_blank'><img class='imagePreview $subclass' src='$filename' title='$file' alt='$file' $htmlSize></a>";
							}	
							else
								echo "<img src='$filename' title='$file' alt='$file' $htmlSize>";
								
							echo "<br>\n";
							displayExifInfo( $filename, $file, $width, $height );
							echo "</$cellTag>\n";
							
							$i++;
							if( $imagesPerLine && ($i % $imagesPerLine == 0) )
								echo "</tr>\n<tr class='imagePreview'>";
						}
					}
				}
				if( $i )
				{
					if( $imagesPerLine )
						echo "</tr></table> <!-- class='imagePreview' -->\n";
					else
						echo "</div><br clear='left'> <!-- class='imagePreview' -->\n";
				}
			}

			if( is_file( $dir . "/left.html" ) || is_file( $dir . "/right.html" ) || $imagePosition )
			{
				echo "</td>\n";
				if( $imagePosition == 3 )
					echo "<td class='inlineImage'><img id='inlineImage' class='inlineImage' $imageHtmlSize src='imagempty.gif'></td>\n";
				if( is_file( $dir . "/right.html" ) )
				{
					echo "<td class='rightCol'>";
					include($dir . "/right.html");
					echo "</td>\n";
				}
				if( $imagePosition == 4 )
					echo "<td class='inlineImage'><img id='inlineImage' class='inlineImage' $imageHtmlSize src='imagempty.gif'></td>\n";
				echo "</tr></table> <!-- class='colLayout' -->\n";
			}
			
			echo "</div> <!-- class='GalleryContent' -->\n";
			/*
				======================================================================================
				display copyright note
				======================================================================================
			*/
			echo "<div class='GalleryFooter' style='clear:left;'>";
			$customview = $dir . "/footer.html";
			if( is_file( $customview ) )
				include( $customview );
			else
			{
				$customview = "footer.html";
				if( is_file( $customview ) )
					include( $customview );
				else
					echo "<font size=-1>ImageScript &copy; 2010-2026 by <a href='mailto:martin@gaeckler.at'>Martin G&auml;ckler</a></font>\n";
			}
			echo "</div>\n";
		?>
	</body>
</html>
