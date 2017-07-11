# White-HTML-Filter      [![Build Status](https://travis-ci.org/lincanbin/White-HTML-Filter.svg?branch=master)](https://travis-ci.org/lincanbin/White-HTML-Filter)

A php-based HTML tag and attribute whitelist filter. 

XSS filtering based on regular or textual replacement is not safe. This filter uses the DOMDocument based on `The Tokenization Algorithm`, which is more secure.

## Requirements

* PHP version 5.3.0 or higher.

## Installation

Install this package via [Composer](https://getcomposer.org/).

```
composer require lincanbin/white-html-filter
```

Or edit your project's `composer.json` to require `lincanbin/white-html-filter` and then run `composer update`.

```json
"require": {
    "lincanbin/white-html-filter": "~1.0"
}
```

## Usage

### Basic Usage

> **Note:** You should have composer's autoloader included `require 'vendor/autoload.php'` (that's obvious.)

Instantiate WhiteHTMLFilter object

```php
use lincanbin\WhiteHTMLFilter;

$html = <<<html
<iframe></iframe>
<div class="contain">
	<span style="color: #f00;">
		test中文
	</span>
</div>
<div class="contain" data-src="xxx" onclick="javascript:alert('xxx');">
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

$filter = new WhiteHTMLFilter();
$filter->loadHTML($html);
$filter->clean();
var_dump($filter->outputHtml());
```

### Configuration
* Remove allowed tags
```php
use lincanbin\WhiteHTMLFilter;
$filter = new WhiteHTMLFilter();
$filter->config->removeAllAllowTag();
//Or
$filter->config->removeFromTagWhiteList('div');
$filter->config->removeFromTagWhiteList(array("div", "table"));
```

* Add new allowed tags 
```php
use lincanbin\WhiteHTMLFilter;
$filter = new WhiteHTMLFilter();
$filter->config->removeAllAllowTag();
$filter->config->modifyTagWhiteList(array(
    "img" => array("alt", "src", "height", "width"),
    "a" => array("href", "rel", "target", "download", "type")
));
```

* Modify allowed HTML global attributes
```php
use lincanbin\WhiteHTMLFilter;
$filter = new WhiteHTMLFilter();
$filter->config->WhiteListHtmlGlobalAttributes = array(
    "class", "style", "title", "data-*"
);
```

* Modify allowed css style (Leave blank to allow everything)
```php
use lincanbin\WhiteHTMLFilter;
$filter = new WhiteHTMLFilter();
$filter->config->WhiteListStyle = array(
    "color", "border", "background", "position"
);
```

* Modify allowed css class (Leave blank to allow everything)
```php
use lincanbin\WhiteHTMLFilter;
$filter = new WhiteHTMLFilter();
$filter->config->WhiteListCssClass = array(
    "container", "title", "sub-title", "sider-bar"
);
```

### Use Custom Filter
```php
use lincanbin\WhiteHTMLFilter;

$html = <<<html
<iframe width="560" height="315" src="https://www.youtube.com/embed/lBOwxXxesBo" frameborder="0" allowfullscreen>
</iframe>
<iframe width="560" height="315" src="https://www.94cb.com/" frameborder="0" allowfullscreen></iframe>
html;
$filter = new WhiteHTMLFilter();
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

$filter->loadHTML($html);
$filter->clean();
var_dump($filter->outputHtml());
```
Result:
```html
<iframe width="560" height="315" src="https://www.youtube.com/embed/lBOwxXxesBo" frameborder="0" allowfullscreen=""/>
<iframe width="560" height="315" src="" frameborder="0" allowfullscreen=""/>

```

### Default Filter Configuration
* [See here](https://github.com/lincanbin/White-HTML-Filter/blob/master/src/WhiteHTMLFilterConfig.php)

## Donate for White HTML Filter

* Alipay: 

![Alipay](https://www.94cb.com/upload/donate_small.png)

* Wechat: 

![Wechat](https://www.94cb.com/upload/donate_weixin_small.png)

* Paypal: 

  https://www.paypal.me/lincanbin
  
  
## License

``` 
Copyright 2017 Canbin Lin (lincanbin@hotmail.com)

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
```