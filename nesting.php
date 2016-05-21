<?php
define("STDERR", fopen("php://stderr", "wt"));
define("k", $_REQUEST["k"] ?? 0.9);
define("n", $_REQUEST["n"] ?? 181);
define("r", $_REQUEST["r"] ?? 2);
define("p2", $_REQUEST["p2"] ?? 100);
define("c", $_REQUEST["c"] ?? 100);
define("showsln", ($_REQUEST["showsln"] ?? "off") !== "off");
define("raw", ($_REQUEST["raw"] ?? "off") !== "off");
define("base64", ($_REQUEST["base64"] ?? "off") !== "off");
$x = 0;
$y = 0;
$l = n / k;
$last = [0, 0];
$image = imagecreate(n * r + 2 * p2 + 1, n * r + 2 * p2 + 1);
$black = imagecolorallocate($image, 0, 0, 0);
$white = imagecolorallocate($image, 255, 255, 255);
imagefill($image, 0, 0, $white);
$first = true;
report();
for($i = 0; $i < c; $i++){
	$y += nl(); report();
	$x += nl(); report();
	$y -= nl(); report();
	$x -= nl(); report();
	$first = false;
}
if(showsln){
	$first = true;
	report(true);
}
imagestring($image, 5, processX($x), processY($y) - 25, "P", $black);
function nl(){
	global $l;
	return $l *= k;
}
function report(bool $round = false){
	global $x, $y, $last, $image, $black, $first;
	imageline($image, processX($last[0]), processY($last[1]), processX($x), processY($y), $black);
	// fwrite(STDERR, "$x, $y\n");
	$last = [$x, $y];
	if($first){
		$string = $round ? ("(" . round($x, 3) . ", " . round($y, 3) . ")") : "($x, $y)";
		imagestring($image, 5, processX($x) - 30, processY($y), $string, $black);
	}
}

function processX(float $x) : int{
	return (int) ($x * r + p2);
}
function processY(float $y) : int{
	return (int) ((n - $y) * r + p2);
}
ob_start();
imagepng($image);
$png = ob_get_contents();
ob_end_clean();
if(raw){
	echo $png;
	header("Content-Type: image/png", true, 201);
	die;
}
if(base64){
	echo base64_encode($png);
	header("Content-Type: image/png", true, 201);
	die;
}
?>
<!DOCTYPE html>

<html>
<head>
	<title>Loop generator</title>
	<script src="//code.jquery.com/jquery-1.12.3.min.js"></script>
</head>
<body>
<form method="get">
	<h3>Parameters:</h3>
	<input id="watchdog" type="checkbox"> Automatically re-generate when any values are changed
	<table>
		<tr>
			<td>Decay ratio</td>
			<td><input id="k" type="number" name="k" value="<?= k ?>" step="0.01"></td>
		</tr>
		<tr>
			<td>Initial length</td>
			<td><input id="n" type="number" name="n" value="<?= n ?>" step="0.01"></td>
		</tr>
		<tr>
			<td>Pixel-to-coordinate radio</td>
			<td><input id="r" type="number" name="r" value="<?= r ?>" step="0.01"></td>
		</tr>
		<tr>
			<td>Padding around 0&le;<em>x</em> &le; 100 and 0 &le; <em>y</em> &le; 100</td>
			<td><input id="p2" type="number" name="p2" value="<?= p2 ?>" step="1"></td>
		</tr>
		<tr>
			<td>Number of cycles of nesting</td>
			<td><input id="c" type="number" name="c" value="<?= c ?>" step="1"></td>
		</tr>
		<tr>
			<td>Show graphical solution</td>
			<td><input id="showsln" type="checkbox" name="showsln" <?= showsln ? "checked" : "" ?>></td>
		</tr>
		<tr>
			<td><input type="submit" value="Generate"></td>
		</tr>
	</table>
</form>
<img id="img" src="data:image/png;base64,<?= base64_encode($png) ?>">
<script>
var watched = collectParams();
function collectParams(){
	return {
		"k": document.getElementById("k").value,
		"n": document.getElementById("n").value,
		"r": document.getElementById("r").value,
		"p2": document.getElementById("p2").value,
		"c": document.getElementById("c").value,
		"showsln": document.getElementById("showsln").checked,
	};	
}
var watchdog = function(){
	var newParams = collectParams();
	if(JSON.stringify(newParams) !== JSON.stringify(watched)){
		watched = newParams;
		if(document.getElementById("watchdog").checked){
			var src = window.location.href.split("?")[0] + "?" + $("form").serialize();
			document.getElementById("img").src = src + "&raw";
			history.replaceState({}, "", src);
		}
	}
	setTimeout(watchdog, 200);
};
watchdog();
console.info("Why would you want to try to mess with the JavaScript...");
</script>
</body>
</html>
