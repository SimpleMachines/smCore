<?php

// Stolen from: http://php.net/php_check_syntax comments
function php_validate_syntax($code)
{
	$braces = 0;
	$inString = 0;

	// First of all, we need to know if braces are correctly balanced.
	// This is not trivial due to variable interpolation which
	// occurs in heredoc, backticked and double quoted strings
	$tokens = token_get_all($code);
	foreach ($tokens as $token)
	{
		if (is_array($token))
		{
			switch ($token[0])
			{
			case T_CURLY_OPEN:
			case T_DOLLAR_OPEN_CURLY_BRACES:
			case T_START_HEREDOC:
				++$inString;
				break;

			case T_END_HEREDOC:
				--$inString;
				break;
			}
		}
		elseif ($inString & 1)
		{
			switch ($token)
			{
			case '`':
			case '"':
				--$inString;
				break;
			}
		}
		else
		{
			switch ($token)
			{
			case '`':
			case '"':
				++$inString;
				break;

			case '{':
				++$braces;
				break;
			case '}':
				if ($inString)
					--$inString;
				else
				{
					--$braces;
					if ($braces < 0)
						break 2;
				}

				break;
			}
		}
	}

	// If $braces is not zero, then we are sure that $code is broken.
	if ($braces)
		return false;

	// Else, if $braces are correctly balanced, then we can safely put
	// $code in a dead code sandbox to prevent its execution.
	// Note that without this sandbox, a function or class declaration inside
	// $code could throw a "Cannot redeclare" fatal error.
	$save = error_reporting(E_COMPILE_ERROR);
	$result = eval('return true; if (0) { ?' . '>' . $code . ' <?php }');
	error_reporting($save);

	return $result;
}