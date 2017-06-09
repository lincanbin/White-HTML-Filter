<?php
/**
 * User: lincanbin
 * Date: 2017/6/9
 * Time: 14:40
 */

use lincanbin\WhiteHTMLFilter;

require(__DIR__ . '/src/WhiteHTMLFilter.php');
require(__DIR__ . '/src/WhiteHTMLFilterConfig.php');

$html = <<<html
<iframe></iframe>
<div class="contain">
	<span style="color: #f00;">
		test中文
	</span>
</div>
<div class="contain">
	<audio controls = "play">
	  <source src="horse.ogg" type="audio/ogg">
	  <source src="horse.mp3" type="audio/mpeg">
	  Your browser does not support the audio element.
	</audio>
</div>
<div class="contain">
	<span style="color: #f00;" class="aabc">test</span>
</div>
<IMG SRC=javascript:alert('XSS')>
html;

//$html = file_get_contents("http://php.net/manual/en/function.strip-tags.php");
$filter = new WhiteHTMLFilter();
$filter->loadHTML($html);

$removedNodes = $filter->clean();
echo "\n\nremoved nodes\n\n";
foreach ($removedNodes as $elem) {
    var_dump($elem->nodeName);
}
var_dump($filter->outputHtml());
