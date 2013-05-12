Multiplayer
===========

A tiny library to build nice HTML embed codes for videos.

Example
-------

Here's how you could create a customized code for a video from Vimeo:

```php
<?php

$Multiplayer = new fg\Multiplayer\Multiplayer( );

echo $Multiplayer->html(
	'http://vimeo.com/47457051',
	array(
		'autoPlay' => true,
		'highlightColor' => 'BADA55'
	)
);

?>
```

This code would produce:

```html
<iframe src="http://player.vimeo.com/video/47457051?autoplay=1&color=BADA55" frameborder="0" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen=""></iframe>
```
