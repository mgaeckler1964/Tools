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
			
			$indent = 16;
	
			$treeTitle = "Galerie";

			/*
				load the header if available within the root directory
			*/
			if( is_file( "imagehead.php" ) )
				include_once( "imagehead.php" );

			// set the title
			echo "<title>$treeTitle</title>\n";
			
			/*
				================================================
				some useful function.
				================================================
			*/
			
			function makeParamString( $dirname )
			{
				return str_replace( ' ', '+', str_replace( '&', '%26', $dirname ) );
			}
			
			function makeDirList( $dir, $level )
			{
				global $indent;
				/*
					======================================================================================
					read directory content
					======================================================================================
				*/
				if( $dh=opendir($dir) )
				{
					while(( $file=readdir($dh)) !== false )
						$files[] = $file;
					
					closedir( $dh );
				}
		
				/*
					======================================================================================
					display subdirectories
					======================================================================================
				*/
				if( count( $files ) > 0 )
				{
					natcasesort( $files );
					
	
					$i = 0;
					foreach( $files as $file )
					{
						$dirname = $dir . "/" . $file;
						if( is_dir( $dirname ) && $file != "." && $file != ".." && $file != "large" && $file != "preview" && $file != "thumbs" )
						{
							
							$dirUrl = makeParamString( $dirname );
							echo "<div class='galleryTreeEntry' style='padding-left:" . ($level*$indent) ."px;'><a href='images.php?DIR=$dirUrl' target='gallery'>$file</a></div>\n";
							makeDirList( $dirname, $level+1 );
						}
					}
				}
			}
		?>
	</head>
	<body class="GalleryTree">
		<?php
			// now dislplay the title again
			echo "<h1 class='GalleryTreeTitle'>$treeTitle</h1>\n";
			echo "<div class='GalleryTree'>\n";

			
			/*
				======================================================================================
				create the custom view part
				======================================================================================
			*/
			$customview = "./customtree.html";
			if( is_file( $customview ) )
			{
				echo "<div class='customTree'>";
				include( $customview );
				echo "<hr class='customTree'>\n";
				echo "</div>\n";	//  class='customTree'
			}

			echo "<div class='galleryTreeEntry'><a href='images.php' target='gallery'>$treeTitle</a></div>\n";
			makeDirList( ".", 1 );	


			
			echo "</div> <!-- class='GalleryTree' -->\n";
		?>
	</body>
</html>
