<?xml version="1.0" encoding="iso-8859-1" ?>
<xsl:stylesheet version='1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>

	<xsl:output method="html" indent="yes" encoding="iso-8859-1" />

	<xsl:template match='/|*'>
		<xsl:apply-templates />
	</xsl:template>

	<xsl:template match="blog">
		<html>
			<head>
				<title><xsl:value-of select="headline"/></title>
				<link rel="stylesheet" type="text/css" href="../gaeckler.css" />
				<link rel="stylesheet" type="text/css" href="blog.css" />
				<link rel="shortcut icon" href="../favicon.ico" type="image/ico" />
			</head>
			<body>
				<xsl:apply-templates />
				<HR />
				<p>
					<a href="index.php">Inhaltsvereichnis</a>.
				</p>
				<P class="copyr">&#169; 1999-2026 by Martin Gäckler <a href="https://www.gaeckler.at/">Österreich</a>&#160;<a href="http://www.gäckler.de/">Deutschland</a></P>
			</body>
		</html>
	</xsl:template>

	<xsl:template match="datum">
		<p><i><xsl:value-of select="."/></i></p>
	</xsl:template>

	<xsl:template match="headline">
		<h1><xsl:value-of select="."/></h1>
	</xsl:template>

	<xsl:template match="teaser">
		<p><b><xsl:value-of select="."/></b></p>
	</xsl:template>

	<xsl:template match="para">
		<p><xsl:copy-of select="node()"/></p>
	</xsl:template>

	<xsl:template match="bild">
    	<figure>
        	<img src="{@src}" alt="{@alt}" style="max-width:100%; height:auto;" />
	        <figcaption><xsl:value-of select="@alt" /></figcaption>
	    </figure>
	</xsl:template>
</xsl:stylesheet>
