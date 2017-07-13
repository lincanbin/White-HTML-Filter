<?php
/**
 * User: lincanbin
 * Date: 2017/6/9
 * Time: 14:40
 */

use lincanbin\WhiteHTMLFilter;

require(__DIR__ . '/src/WhiteHTMLFilter.php');
require(__DIR__ . '/src/WhiteHTMLFilterConfig.php');


$passed = 0;
$failed = 0;
$filter = new WhiteHTMLFilter();
$filter->config->WhiteListStyle = array('color');
$filter->config->WhiteListCssClass = array('contain', 'sider');
$urlFilter = function($url) {
    $regex = '~
  ^(?:https?://)?                           # Optional protocol
   (?:www[.])?                              # Optional sub-domain
   (?:youtube[.]com/embed/|youtu[.]be/) # Mandatory domain name (w/ query string in .com)
   ([^&]{11})                               # Video id of 11 characters as capture group 1
    ~x';
    return (preg_match($regex, $url) === 1) ? $url : '';
};

$iframeRule = array(
    'iframe' => array(
        'src' => $urlFilter,
        'width',
        'height',
        'frameborder',
        'allowfullscreen'
    )
);
$filter->config->removeFromTagWhiteList('table');
$filter->config->removeFromTagWhiteList(array('tr', 'th', 'td'));
$filter->config->modifyTagWhiteList($iframeRule);

//No filter
// PHP 5.4+
if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
    test(
        '<div class="contain"><span style="color: #f00;"><p>test中文</p>
<br/><br>line2</span></div>',
        '<div class="contain"><span style="color:#f00;"><p>test中文</p>
<br/><br/>line2</span></div>');
}


//User filer
test(
    '<iframe width="560" height="315" src="https://www.youtube.com/embed/lBOwxXxesBo" frameborder="0" allowfullscreen></iframe>',
    '<iframe width="560" height="315" src="https://www.youtube.com/embed/lBOwxXxesBo" frameborder="0" allowfullscreen="">
</iframe>'
);

//User filer
test(
    '<iframe width="560" height="315" src="https://www.94cb.com/" frameborder="0" allowfullscreen></iframe>',
    '<iframe width="560" height="315" src="" frameborder="0" allowfullscreen="">
</iframe>'
);

//Tag filter
test(
    '<script type="text/javascript" charset="utf-8" src="/static/js/jquery.js"></script>',
    ''
);
test(
    '<img src="http://127.0.0.1/upload/donate_small.png" width="251" height="250" />',
    '<img src="http://127.0.0.1/upload/donate_small.png" width="251" height="250"/>'
);
test(
    '<IMG SRC=javascript:alert(\'XSS\')>',
    '<img src=""/>'
);

//Unclosed tag filter
test(
    '<div>xxxx</div><div>dddd',
    '
<div>xxxx</div>
<div>dddd</div>
'
);

//attributes filter
test(
    '<div class="contain" data-src="xxx" onclick="javascript:alert(\'xxx\');">
<audio controls = "play">
<source src="horse.ogg" type="audio/ogg" /><source src="horse.mp3" type="audio/mpeg" />
Your browser does not support the audio element.
</audio>
</div>',
    '<div class="contain" data-src="xxx">
<audio controls="play">
<source src="horse.ogg" type="audio/ogg"/>
<source src="horse.mp3" type="audio/mpeg"/>
Your browser does not support the audio element.
</audio>
</div>'
);


//CSS classes filter
test(
    '<div class="contain sider float-right">right</div>',
    '<div class="contain sider">right</div>'
);


//CSS styles filter
test(
    '<span style="color: #f00;font-size: 19px;float:right;" class="aabc">test</span>',
    '<span style="color:#f00;" class="">test</span>'
);

//Url filter
test(
    '<a href="JavaScript:alert("xss");">link</a>',
    '<a href="">link</a>'
);

//Invalid tag with text content filter
test(
    '<div class="form"><form action="login" method="post">username: lincanbin  <button>Login</button></form></div>',
    '<div class="">username: lincanbin  Login</div>'
);


//Unclosed tag
test(
    '<div class="">content</div><div>',
    '<div class="">content</div><div>
</div>'
);


echo "\n\n\033[32m $passed passed \033[0m\n\n";

if ($failed === 0) {
    exit(0);
} else {
    echo "\033[31m $failed failed \033[0m\n\n";
    exit(1);
}

function test($input, $assert)
{
    global $filter, $passed, $failed;
    echo "\n\033[33m -------------------------------------------------------- \033[0m\n";
    $startTime = microtime(true);
    $filter->loadHTML($input);
    $removedNodes = $filter->clean();
    $result = $filter->outputHtml();
    $timeCost = number_format((microtime(true) - $startTime) * 1000, 3);
    echo "\ninput:             ";
    var_dump($input);

    echo "\nassert:            ";
    var_dump($assert);

    echo "\nresult:            ";
    var_dump($result);

    echo "\n\nremoved nodes: \n";
    foreach ($removedNodes as $elem) {
        echo $elem . "\n";
    }

    if (delete_empty_char($result) === delete_empty_char($assert)) {
        echo "\n\033[32m passed $timeCost ms\033[0m\n";
        $passed++;
    } else {
        echo "\n\033[31m failed $timeCost ms\033[0m\n";
        $failed++;
    }
    echo "\n\033[33m -------------------------------------------------------- \033[0m\n\n\n\n";
}

function delete_empty_char($string)
{
    $search = array("　", "\n", "\r", "\t");
    $replace = array("", "", "", "");
    return str_replace($search, $replace, $string);
}