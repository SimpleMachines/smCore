<?php

// Needed for execution (and compiling.)
// For debugging.
require(__DIR__ . '/Errors.php');
// For makeTemplateName().
require(__DIR__ . '/Expression.php');
// For VERSION, callTemplate().
require(__DIR__ . '/Template.php');
// For simple handling of many templates.
require(__DIR__ . '/TemplateList.php');

// Needed for only compiling.
require(__DIR__ . '/Exception.php');
require(__DIR__ . '/ExceptionFile.php');
require(__DIR__ . '/Source.php');
require(__DIR__ . '/SourceFile.php');
require(__DIR__ . '/Prebuilder.php');
require(__DIR__ . '/Builder.php');
require(__DIR__ . '/Overlay.php');
require(__DIR__ . '/Parser.php');
require(__DIR__ . '/Token.php');
require(__DIR__ . '/StandardElements.php');
require(__DIR__ . '/Theme.php');
require(__DIR__ . '/Filters.php');