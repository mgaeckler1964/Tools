var	destMargin			= 50;
var	smalMargin			= 512;
var opaqueSpeed			= 100;
var growSpeed			= 10;
var growIncrement		= 10;
var imageWidth			= 0;
var imageHeight			= 0;

var	intervalId			= null;
var	loaderId			= null;
var naviTimeout			= null;
var showTimerId			= null;
var showInterval		= 0;

var opacity				= 0;
var opacityInterval		= 0;

var currentImageIndex	= -1;
var imageSizePercent	= 100;

var margin				= smalMargin;
var navigatorOn			= false;

var imageArray			= new Array();

var clientHeight		= 600;
var clientWidth			= 800;

var status				= "";
var progressWidth		= 1;


/*
	=============================
	functions handling image size
	=============================
*/
function showImagePercent()
{
	var element = document.getElementById( 'imageSizeInfo' );
	element.innerHTML = Math.floor(imageSizePercent) + "%";
}

function scalePicture( element )
{
	if( currentImageIndex >= 0 && imageArray[currentImageIndex].img.width && imageArray[currentImageIndex].img.height )
	{
		showImagePercent();
		var width = Math.floor(imageArray[currentImageIndex].img.width * imageSizePercent / 100);
		var height = Math.floor(imageArray[currentImageIndex].img.height * imageSizePercent / 100);

		element.width = width;
		element.height = height;
		
		element = document.getElementById( 'picFrame' );
		if( width <= clientWidth && height<=clientHeight )
		{
			element.style.maxWidth = width + "px";
			element.style.maxHeight = height + "px";
			element.style.overflow = "hidden";
		}
		else
		{
			element.style.maxWidth = (width+20) + "px";
			element.style.maxHeight = (height+20) + "px";
			element.style.overflow = "scroll";
		}
	}
}

function makeImageBigger()
{
	if( imageSizePercent < 400 )
	{
		imageSizePercent += 25;
		scalePicture( document.getElementById( 'pic' ) );
	}
}

function makeImageSmaller()
{
	if( imageSizePercent >= 50 )
	{
		imageSizePercent -= 25;
		scalePicture( document.getElementById( 'pic' ) );
	}
}

function fullSizeImage()
{
	imageSizePercent = 100;
	scalePicture( document.getElementById( 'pic' ) );
}

function areaSizeImage( areaWidth, areaHeight )
{
	if( currentImageIndex >= 0 )
	{
		var width = imageArray[currentImageIndex].img.width;
		var height = imageArray[currentImageIndex].img.height;

		if( !width || !height )
		{
			width = imageArray[currentImageIndex].maxWidth;
			height = imageArray[currentImageIndex].maxHeight;
		}
		
		if( width && height )
		{
			var widthRatio = areaWidth ? width / areaWidth : 0;
			var heightRatio = areaHeight ? height / areaHeight : 0;

			var ratio;
			if( widthRatio > heightRatio )
				ratio = widthRatio;
			else
				ratio = heightRatio;
				
			if( !ratio )
				ration = 1;
				
			imageSizePercent = 100 / ratio;
			scalePicture( document.getElementById( 'pic' ) );
		}
	}
}

function screenSizeImage()
{
	areaSizeImage( clientWidth, clientHeight );
}

/*
	============================
	functions handling navigator
	============================
*/
function showNavigator()
{
	var element = document.getElementById( 'pic' );
	element.style.cursor = 'auto';

	if( currentImageIndex >= 0 &&  !intervalId )	// do not show during growth
	{
		var element = document.getElementById( 'imageNavigator' );
		element.style.visibility = 'visible';
	}
	navigatorOn = true;

	if( naviTimeout )
		window.clearTimeout( naviTimeout );
	naviTimeout	= window.setTimeout( "hideCursor()", 5000 );
}

function hideNavigator()
{
	var element = document.getElementById( 'imageNavigator' );
	element.style.visibility = 'hidden';
	navigatorOn = false;
	
	if( naviTimeout )
	{
		window.clearTimeout( naviTimeout );
		naviTimeout = null;
	}
}

function hideCursor()
{
	var element = document.getElementById( 'pic' );
	element.style.cursor = 'none';
	hideNavigator();
}

/*
	==============================
	functions handling image array
	==============================
*/
function galleryImage( imgFile, maxWidth, maxHeight )
{
	this.imgFile = imgFile;
	this.maxWidth = maxWidth;
	this.maxHeight = maxHeight;
	this.img = new Image();
}

function addGalleryImage( imgFile, maxWidth, maxHeight )
{
	var theImage = new galleryImage( imgFile, maxWidth, maxHeight );
	imageArray[imageArray.length] = theImage;
}

function showNextImage()
{
	if( currentImageIndex >= 0 )
	{
		if( currentImageIndex +1 < imageArray.length )
			currentImageIndex++;
		else
			currentImageIndex = 0;

		progressWidth = 1;
		loadImage();
	}
}

function showPrevImage()
{
	if( currentImageIndex >= 0 )
	{
		if( currentImageIndex > 0 )
			currentImageIndex--;
		else
			currentImageIndex = imageArray.length-1;

		progressWidth = 1;
		loadImage();
	}
}


function startShow( interval )
{
	showInterval = interval;

	if( showTimerId )
	{
		window.clearInterval( showTimerId );
		showTimerId = null;
	}
	if( interval > 0 )
	{
		showTimerId = window.setInterval( "showNextImage()", interval );
	}
}

/*
	================
	show new picture
	================
*/
function makeImageInvisible()
{
	var element = document.getElementById( 'pic' );

	element.src = "imagempty.gif";
	opacity = 0;
	element.style.opacity = opacity;
	element.style.filter = "alpha(opacity="+(opacity*100)+")";
}

function makeImageVisible()
{
	var element = document.getElementById( 'pic' );
	if( !opacityInterval )
	{
		opacity = 0;
		opacityInterval = window.setInterval( "makeImageVisible();", opaqueSpeed );
	}
	else if( opacity < 0.9 )
	{
		if( element.src != imageArray[currentImageIndex].img.src )
			element.src = imageArray[currentImageIndex].img.src;
		opacity += 0.1;
	}
	else
	{
		opacity = 1;
	}
	
	element.style.opacity = opacity;
	element.style.filter = "alpha(opacity="+(opacity*100)+")";

	if( opacity >= 1 && opacityInterval )
	{
		window.clearInterval( opacityInterval );
		opacityInterval = 0;
	}
}

function showInlinePic( imgFile )
{
	var	element = document.getElementById( 'inlineImage' );
	element.src = imgFile;
}

/*
	load the currently selected picture, when loaded hide the currently shown picture and display the new picture
*/
function loadImage()
{
	if( currentImageIndex >= 0 )
	{
		var element = document.getElementById( "imagePos" );
		element.innerHTML = (currentImageIndex+1) + "/" + imageArray.length;
			
		element = document.getElementById( 'pic' );
		if( progressWidth >= clientWidth 
		|| (imageArray[currentImageIndex].img.src && imageArray[currentImageIndex].img.complete) 
		|| imageArray[currentImageIndex].img.width 
		|| imageArray[currentImageIndex].img.height
		)
		{
			if( loaderId )
			{
				window.clearInterval( loaderId );
				loaderId = null;
			}

			status = "";
			window.status = imageArray[currentImageIndex].img.width + "px * " + imageArray[currentImageIndex].img.height + "px ";

			makeImageInvisible();

			
			var  scaleWidth, scaleHeight;
			
			var tmpString = imageWidth + "x";
			if( tmpString.toUpperCase() == "AUTOX" )
				scaleWidth = clientWidth;
			else
				scaleWidth = imageWidth;
				
			tmpString = imageHeight + "x";
			if( tmpString.toUpperCase() == "AUTOX" )
				scaleHeight = clientHeight;
			else
				scaleHeight = imageHeight;
				
			if( scaleWidth || scaleHeight )
				areaSizeImage( scaleWidth, scaleHeight );
			else
				scalePicture( element );

			element = document.getElementById( 'progressBar' );
			element.style.display = "none";
			element = document.getElementById( 'progress' );
			element.style.width = "1px";
			progressWidth = 1;

			makeImageVisible();
		}
		else
		{
			status += "*";
			window.status = status;

			imageArray[currentImageIndex].img.src = imageArray[currentImageIndex].imgFile;
			if( !loaderId )
				loaderId = window.setInterval( "loadImage();", 100 );

			element = document.getElementById( 'progressBar' );
			element.style.display = "block";
			element = document.getElementById( 'progress' );
			element.style.width = progressWidth + "px";
			progressWidth += clientWidth/50;
		}
	}
}

/*
	========================
	create the picture frame
	========================
*/
function showPic( imgFile, maxWidth, maxHeight )
{
	var element;

	// setup  margin to a small frame
	margin = smalMargin;

	// search picture in our picture array
	currentImageIndex = -1;
	for( i=0; i<imageArray.length; i++ )
	{
		if( imageArray[i].imgFile == imgFile )
		{
			currentImageIndex = i;
			break;
		}
	}
	if( currentImageIndex < 0 )
	{
		currentImageIndex = imageArray.length;
		addGalleryImage( imgFile, maxWidth, maxHeight );
	}


	// setup the client size
	if( window.innerHeight )
	{
		clientHeight = window.innerHeight - 2*destMargin;
	}
	else if( document.documentElement && document.documentElement.clientHeight )
	{
		clientHeight = document.documentElement.clientHeight - 2*destMargin;
	}
	else if( document.body && document.body.clientHeight )
	{
		clientHeight = document.body.clientHeight - 2*destMargin;
	}
	
	if( window.innerWidth )
	{
		clientWidth = window.innerWidth - 2*destMargin;
	}
	else if( document.documentElement && document.documentElement.clientWidth )
	{
		clientWidth = document.documentElement.clientWidth - 2*destMargin;
	}
	else if( document.body && document.body.clientWidth )
	{
		clientWidth = document.body.clientWidth - 2*destMargin;
	}

	/* adjust destMargin for small screens */
	if( (clientWidth < 800 || clientHeight < 600) && destMargin > 0 )
	{
		clientWidth += 2*destMargin;
		clientHeight += 2*destMargin;
		
		destMargin = 0;
	}
	element = document.getElementById( 'gray' );
	element.style.display = 'block';
	element.style.visibility = 'visible';

	element = document.getElementById( 'picFrame' );

	element.style.top = smalMargin + "px";
	element.style.left = smalMargin + "px";
	element.style.right = smalMargin + "px";
	element.style.bottom = smalMargin + "px";
	element.style.visibility = 'visible';

	loadImage();

	if( intervalId )
	{
		window.clearInterval( intervalId );
		intervalId = null;
	}

	if( margin > destMargin )
		intervalId = window.setInterval( "increasFrame()", growSpeed );
	else
	{
		element = document.getElementById( 'imageNavigator' );
		element.style.top = margin+"px";
		element.style.left = margin+"px";
	}
}

// make picture frame bigger
function increasFrame()
{
	var element = document.getElementById( 'picFrame' );

	if( margin > (destMargin+growIncrement) )
	{
		margin -= growIncrement;
		element.style.top = margin + "px";
		element.style.left = margin + "px";
		element.style.right = margin + "px";
		element.style.bottom = margin + "px";
	}
	else
	{
		margin = destMargin;
		element.style.top = destMargin + "px";
		element.style.left = destMargin + "px";
		element.style.right = destMargin + "px";
		element.style.bottom = destMargin + "px";

		if( intervalId )
		{
			window.clearInterval( intervalId );
			intervalId = null;
		}

		element = document.getElementById( 'imageNavigator' );
		element.style.top = margin+"px";
		element.style.left = margin+"px";
		if( navigatorOn )
			element.style.visibility = "visible";

		if( showInterval > 0 )
			startShow( showInterval );
	}
}

/*
	===================================
	hide current picture (remove frame)
	===================================
*/
function hidePicture()
{
	if( showTimerId )
	{
		window.clearInterval( showTimerId );
		showTimerId = null;
	}
	if( intervalId )
	{
		window.clearInterval( intervalId );
		intervalId = null;
	}
	if( loaderId )
	{
		window.clearInterval( loaderId );
		loaderId = null;
	}
	progressWidth = 1;

	hideNavigator();
	intervalId = window.setInterval( "decreaseFrame()", growSpeed );
}

// make picture frame smaller or hide completely
function decreaseFrame()
{
	var element = document.getElementById( 'picFrame' );

	if( margin < smalMargin )	// not yet small enough?
	{
		margin += growIncrement;
		element.style.top = margin + "px";
		element.style.left = margin + "px";
		element.style.right = margin + "px";
		element.style.bottom = margin + "px";

		element = document.getElementById( 'imageNavigator' );
		element.style.top = margin+"px";
		element.style.left = margin+"px";
	}
	else
	{
		margin = smalMargin;
		element.style.top = smalMargin + "px";
		element.style.left = smalMargin + "px";
		element.style.right = smalMargin + "px";
		element.style.bottom = smalMargin + "px";

		hideNavigator();
		element.style.visibility = 'hidden';

		makeImageInvisible();

		element = document.getElementById( 'gray' );
		element.style.visibility = 'hidden';

		currentImageIndex = -1;

		if( intervalId )
		{
			window.clearInterval( intervalId );
			intervalId = null;
		}
	}
}

/*
	event handling
	=========================
*/
function keys( theEvent )
{
	var keyCode = 0;
	var element = document.getElementById( 'picFrame' );
	if( element.style.visibility == 'visible' )
	{
		if( !theEvent )
			theEvent = window.event;

		if( theEvent.which )
			keyCode = theEvent.which;
  		else if( theEvent.keyCode )
			keyCode = theEvent.keyCode;

		if( keyCode == 27 )
			hidePicture();
		else if( keyCode == 43 ) // +-
			makeImageBigger();
		else if( keyCode == 45 ) // --
			makeImageSmaller();
		else if( keyCode == 39 || keyCode == 62 || keyCode == 110 || keyCode == 78 ) // ->, >, n, N
			showNextImage();
		else if( keyCode == 37 || keyCode == 60 || keyCode == 118 || keyCode == 86 ) // <-, <, v, V
			showPrevImage();
		else if( keyCode == 32 ) // <space>
			fullSizeImage();
		else if( keyCode == 13 ) // <enter>
			screenSizeImage();
		else
			alert( keyCode );

		return false;
	}

	return true;
}
document.onkeypress = keys;

/*
	create the image elements
	=========================
*/
document.write(	'<div id="gray" style="visibility: hidden; position: fixed; z-index: 1; top:0; left: 0; right:0; bottom:0; background-color:#000000; opacity:0.7; filter:alpha(opacity=70);">&nbsp;</div>' );
document.write(	'<div id="picFrame" class="picFrame" onMouseOver="showNavigator();" onMouseMove="showNavigator();" onMouseOut="hideNavigator();"' );
	document.write(	'style="visibility: hidden; position: fixed; z-index: 10; top:50px; left: 50px; right:50px; bottom:50px;"' );
document.write(	'>' );
	document.write( '<table id="progressBar" width="100%" style="position:absolute; top:60px;"><tr><td id="progress" style="width:1px; background-color:#FFFFFF;">&nbsp;</td><td>&nbsp;</td></table>' );
	document.write(	'<center><img id="pic"></center>' );
	document.write(	'<div class="imageNavigator" id="imageNavigator" style="z-index:20;">' );

		document.write(	'<span id="imageNav" class="imageNav">' );
			document.write(	'<a class="prevImage" href="javascript:showPrevImage();" title="Vorheriges Bild (<)">' );
			if( typeof imagePrev != 'undefined' && imagePrev != null )
				document.write( "<img class='imagePrev' src='" + imagePrev + "' title='Vorheriges Bild (<)'>" );
			else
				document.write(	'&lt;&lt;' );
			document.write(	'</a>&nbsp;' );

			document.write(	'<a class="nextImage" href="javascript:showNextImage();" title="N‰chstes Bild (>)">' );
			if( typeof imageNext != 'undefined' && imageNext != null )
				document.write( "<img class='imageNext' src='" + imageNext + "' title='N‰chstes Bild (>)'>" );
			else
				document.write(	'&gt;&gt;' );
			document.write(	'</a>&nbsp;' );
			document.write(	'<br><span class="imagePos" id="imagePos"></span>&nbsp;' );
		document.write(	'</span>' );
		document.write(	'<span id="imageShow" class="imageShow">' );
			document.write( '<form>' );
				document.write( '<select name="showTimer" onChange="startShow( this.value );">' );
					document.write( '<option value="0" selected>Aus</option>' );
					document.write( '<option value="5000">5s</option>' );
					document.write( '<option value="20000">20s</option>' );
					document.write( '<option value="60000">60s</option>' );
				document.write( '</select>' );
			document.write( '</form>' );
		document.write(	'</span>' );

		document.write(	'<span id="imageZoom" class="imageZoom">' );
			document.write(	'<a class="imageZoom" href="javascript:makeImageSmaller();" title="Bild Verkleinern (-)">' );
			if( typeof imageSmaller != 'undefined' && imageSmaller != null )
				document.write( "<img class='imageSmaller' src='" + imageSmaller + "' title='Bild Verkleinern (-)'>" );
			else
				document.write(	'&nbsp;-&nbsp;' );
			document.write(	'</a>&nbsp;' );
			
			document.write(	'<a class="imageFullSize" id="imageFullSize" href="javascript:fullSizeImage();" title="Bildgrˆﬂe 100% (Leer)">' );
			if( typeof image100 != 'undefined' && image100 != null )
				document.write( "<img class='image100' src='" + image100 + "' title='Bildgrˆﬂe 100% (Leer)'>" );
			else
				document.write(	'100%' );
			document.write(	'</a>&nbsp;' );
			document.write(	'<a class="imageFullSize" id="imageScreenSize" href="javascript:screenSizeImage();" title="Bildgrˆﬂe auf Bildschirm (Enter)">' );
			if( typeof imageScreen != 'undefined' && imageScreen != null )
				document.write( "<img class='imageScreen' src='" + imageScreen + "' title='Bildgrˆﬂe auf Bildschirm (Enter)'>" );
			else
				document.write(	'Bildschirm' );
			document.write(	'</a>&nbsp;' );
			document.write(	'<a class="imageZoom" href="javascript:makeImageBigger();" title="Bild Vergrˆﬂern (+)">' );
			if( typeof imageBigger != 'undefined' && imageBigger != null )
				document.write( "<img class='imageBigger' src='" + imageBigger + "' title='Bild Vergrˆﬂern (+)'>" );
			else
				document.write(	'&nbsp;+&nbsp;' );
			document.write(	'</a>' );
			document.write(	'<br><span class="imageSizeInfo" id="imageSizeInfo">100%</span>&nbsp;' );
		document.write(	'</span>' );
	
		document.write(	'<a class="closeImage" href="javascript:hidePicture();" title="Schlieﬂen (ESC) ">' );
		if( typeof imageClose != 'undefined' && imageClose != null )
			document.write( "<img class='imageClose' src='" + imageClose + "'  title='Schlieﬂen (ESC)'>" );
		else
			document.write(	'Schlieﬂen' );
		document.write(	'</a>&nbsp;' );


	document.write(	'</div>' );
document.write(	'</div>' );
