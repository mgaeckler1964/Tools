<?php
	$imageFile = $_GET["IMAGE"];
	if( isset($_GET["WIDTH"]) )
	{
		$newWidth = $_GET["WIDTH"]; 
		if( $newWidth == "" )
			$newWidth = 320;
	}
	else
		$newWidth = 320;
		
	if( isset($_GET["CACHE"]) )
	{
		$cache = $_GET["CACHE"];
		if( $cache == "" )
			$cache = 0;
	}
	else
		$cache = 0;
	
	if( $imageFile=="" || substr( $imageFile, 0, 2 ) != "./" || strpos( $imageFile, "/." ) > 0  || !is_file( $imageFile ) )
	{
		echo "Keine oder eine ungültige Datei angegeben $imageFile";
		exit( -1 );
	}
	

	if( $cache )
	{
		ini_set('gd.jpeg_ignore_warning', 1);

		$slashPos = strrpos( $imageFile, '/' );
		if( $slashPos )
		{
			$cacheFile = substr( $imageFile, 0, $slashPos ) . "/thumbs";
			if( !file_exists($cacheFile) )
				mkdir( $cacheFile );
			chmod( $cacheFile, 0777 );
			$cacheFile = $cacheFile . substr( $imageFile, $slashPos );
		}
		
		if( is_file( $cacheFile ) )
		{
			chmod( $cacheFile, 0777 );
		    
			$srcImg = imageCreateFromJpeg( $cacheFile );
			if( $srcImg )
			{
				$srcWidth = imagesx($srcImg);
				if( $srcWidth == $newWidth )
				{
					header( "Content-Type: IMAGE/jpeg" );
					imagejpeg( $srcImg );
/***/				exit( 0 );
				}
			}
		}
	}
		
	$srcImg = imageCreateFromJpeg( $imageFile );
	if( !$srcImg )
	{
		echo "Bild $imageFile konnte nicht geladen werden";
		exit( -1 );
	}
	

	$srcWidth = imagesx($srcImg);
	$srcHeight = imagesy($srcImg);
	
	//calculate the image ratio
	$imgRatio = ($srcWidth / $srcHeight);
	
	$newHeight = ($newWidth / $imgRatio);

	$newImage = imagecreatetruecolor($newWidth,$newHeight);
	ImageCopyResized($newImage, $srcImg,0,0,0,0, $newWidth, $newHeight, $srcWidth, $srcHeight);

	if( $cache )
	{
		imageJpeg( $newImage, $cacheFile, 100 );
		chmod( $cacheFile, 0777 );
	}
	
	header( "Content-Type: image/JPEG" );
	imagejpeg( $newImage );
	
?>
