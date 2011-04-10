// Fixing WinIE supporting min-width css property
function bodySize () {
	sObj = document.getElementsByTagName("body")[0].style;
	sObj.width = (document.documentElement.clientWidth<1000) ? "1000px" : "100%";
}

function init () {
	bodySize();
}

onload = init;
onresize = bodySize;