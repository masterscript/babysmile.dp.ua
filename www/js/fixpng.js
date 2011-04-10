// fixPNG(); http://www.tigir.com/js/fixpng.js (author Tigirlas Igor)
function fixPNG(element)
{
	if (/MSIE (5\.5|6).+Win/.test(navigator.userAgent))
	{
		var src;
		
		if (element.tagName=='IMG')
		{
			if (/\.png$/.test(element.src))
			{
				src = element.src;
				element.src = "blank.gif";
			}
		}
		else
		{
			src = element.currentStyle.backgroundImage.match(/url\("(.+\.png)"\)/i)
			if (src)
			{
				src = src[1];
				element.runtimeStyle.backgroundImage="none";
			}
		}
		
		if (src) {
			element.runtimeStyle.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + src + "',sizingMethod='crop')";
		}
	}
}

/*var arVersion = navigator.appVersion.split("MSIE")
var version = parseFloat(arVersion[1])

function fixPNG(myImage) 
{
    if ((version >= 5.5) && (version < 7) && (document.body.filters)) 
    {
       var imgID = (myImage.id) ? "id='" + myImage.id + "' " : ""
	   var imgClass = (myImage.className) ? "class='" + myImage.className + "' " : ""
	   var imgTitle = (myImage.title) ? 
		             "title='" + myImage.title  + "' " : "title='" + myImage.alt + "' "
	   var imgStyle = "display:inline-block;" + myImage.style.cssText
	   var strNewHTML = "<span " + imgID + imgClass + imgTitle
                  + " style=\"" + "width:" + myImage.width 
                  + "px; height:" + myImage.height 
                  + "px;" + imgStyle + ";"
                  + "filter:progid:DXImageTransform.Microsoft.AlphaImageLoader"
                  + "(src=\'" + myImage.src + "\', sizingMethod='crop');\"></span>"
	   myImage.outerHTML = strNewHTML	  
    }
}*/