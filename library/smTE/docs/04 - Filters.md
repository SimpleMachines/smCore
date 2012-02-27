# Filters

## Overview

Filters are used on variable and language references to manipulate the value that
is output. There are currently 33 filters built into the smCore template engine,
and developers can add their own filters with a simple method call.

## Filter Syntax

Formatting is very easy to use. In order to add a filter to an existing reference,
add `|filter_name($param, 'eters')` at the end of the reference. Multiple filters
can be added to a single reference, and are evaluated from left to right.

In most cases, expressions with filters might look like the following:

	{$birthday |date}
		Formats a birthday using the default date format.

	{$today.date |date("g:i A")}
		Formats with a custom format.

	{$name |upper}
		Shows a name in all capital letters.

	{#welcome_message |lower |raw}
		Formats a language string into lowercase, and does not escape it.

	{$price |number}
		Formats $price into something like 2,000.57

	{$thing |my_filter($first, $second.param, 'third', #fourth)}
		Applies a custom filter named 'my_formatter' with many parameters.

On the code level, filters are passed the current value of the reference (a variable
or language string), and return a modified version of that value.

## Default Filters

### contains
If the variable is an array, returns true if the parameter is in the array.
If the variable is a string, returns true if the parameter can be found in the string.
Returns false in all other cases.

	{$scores |contains('95')}
	{$alphabet |contains('abc')}

### date
Format a timestamp into human-readable format.

	{$birthday |date}
		Format $birthday using the default date format.

	{$birthday |date("n/j/Y @ g:i:s A")}
		Format $birthday with a custom formatting string.

### default
If the value is empty according to PHP's `empty()` function, use the passed parameter instead.

	{$name |default('John Doe')}

### divisibleby
Returns true if the variable is a number divisible by the parameter.

	{$age |divisibleby(3)}

### empty
Returns true if the variable is empty, using PHP's `empty()` function, otherwise false.

	{$glass |empty}

### escape
If the value would not be escaped, such as inside a CDATA block, escape it.

	{$unsafe_code |escape}

### even
Returns true if the variable is an even integer (modulo 2 = 0), otherwise false.

	{$age |even}

### float
Format the value as a float, with optional precision

	{$rating |float}
		Might return 4.81516

	{$rating |float(3)}
		If $rating is 4.815162342, return 4.815

### join
Returns the elements in the value joined by the parameter.

	{$tags |join(", ")}

### json
Returns a JSON representation of the data in the variable.

	{$person |json}

### length
Returns the length of the variable. Arrays return their count, and strings return their length.

	{$posts |length}
	{$name |length}

### lower
Formats the entire string into lowercase.

	{$whisper_this |lower}

### ltrim
Removes whitespace from the start (left) of the string.

	{$text |ltrim}

### money
Format the value as a monetary value, with an optional formatting string and locale.

	{$price |money}
		Might return $1,400.17

	{$price |money("%.2n")}
		Format according to a formatting string.

	{$price |money("%.2n", "it_IT")}
		Format Italian style!

### nl2br
Convert newline characters (such as from `|wordwrap`) into HTML break tags.

	{$poem |wordwrap(25) |nl2br}

### null
Returns true if the variable is null, otherwise false.

	{$IQ |null}

### odd
Returns true if the variable is an odd integer (modulo 2 = 1), otherwise false.

	{$age |odd}

### random
Returns a random value from an array, optionally returns a random selection.

	{$messages |random}
	{$messages |random(3)}

### raw
If the value would be escaped, as it normally would, do not escape it.

	{$safe_html |raw}

### rtrim
Removes whitespace from the end (right) of the string.

	{$text |rtrim}

### stripchars
Removes the characters in the parameter from the value.

	{$speech |stripchars('qxz')}

### striptags
Removes HTML tags from the value, and optionally allows you to keep some.

	{$var |striptags}
	{$post |striptags('b i strong em tt')}

### time
Same as `|date`, but without a parameter it only displays the time.

	{$var |time}
	{$var |time("g:i A")}

### trim
Removes whitespace from the start and end of the string.

	{$text |trim}

### truncate
Cut off the value after a certain amount of characters, and optionally append a string if it does get cut.

	{$var |truncate(15)}
	{$var |truncate(15, '...')}

### truncatewords
Cut off the value after a certain amount of words, and optionally append a string if it does get cut.

	{$var |truncatewords(12)}
	{$var |truncatewords(12, $read_more_button)}

### ucfirst
Capitalizes the first chracter of the string, lowercases everything else.

	{$text |ucfirst}

### ucwords
Capitalizes the first character of every word.

	{$text |ucwords}

### upper
Formats the entire string into uppercase.

	{$scream_this |upper}

### urlencode
Returns a URL-safe version of the value.

	{$var |urlencode}
		Would turn "hello world" into "hello%20world"

### wordcount
Returns the number of words in the variable.

	{$poem |wordcount}

### wordwrap
Wrap the text every X characters, word-aware.

	{$var |wordwrap(80)}

### wrap
Wrap the text every X characters, regardless of words.

	{$var |wrap(80)}