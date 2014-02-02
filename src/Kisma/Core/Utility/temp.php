I know this is an old question but it still hasn't been answered properly.

`isset` *only* tests if a key exists *and* not null. This is any important distinction.  See the examples: http://us1.php.net/isset

To properly test if an array contains a key, `array_key_exists` should be used as stated elsewhere on this page.

On the other hand, to test if an object contains a property, the `property_exists` method should be used. `property_exists()` returns `TRUE` even if the property has a `NULL` value.

So, your code should look like this:

	<?php
	if ( false !== property_exists( $content, 'images' ) )
	{
		echo '<pre>' . print_r( $content->images, true ) . '</pre>';
	}
	?>
