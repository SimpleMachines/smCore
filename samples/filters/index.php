<?php

$time_st = microtime(true);

require(dirname(__DIR__) . '/include.php');

$theme = new SampleTheme(__DIR__, __DIR__);
$theme->loadTemplates('templates');
$theme->addTemplate('main');

$theme->context = array(
	'grades' => array(82, 90, 95, 99),
	'alphabet' => 'abcdefghijklmnopqrstuvwxyz',
	'time' => time(),
	'empty_string' => '',
	'empty_array' => array(),
	'nine' => 9,
	'ten' => 10,
	'pi' => M_PI,
	'malicious_js' => '<script>document.write("<div class=\"malicious\">This should be escaped!</div>");</script>',
	'needs_trim' => '          ten spaces!          ',
	'html' => '<strong>This is HTML</strong>',
	'huge_array' => array(
		1 => 'First',
		'Llama',
		time(),
		array(
			new DateTime('2000-01-01'),
			0.777,
		),
	),
	'hundred_elements' => range(1, 100),
	'name' => 'Unknown W. Brackets',
	'name_lower' => 'unknown w. brackets',
	'greetings' => array(
		'Hello!',
		'Welcome!',
		'Good day!',
		'Bienvenido!',
		'Arrrr!',
	),
	'lorem_ipsum' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam sagittis enim in mi tempor suscipit. Duis sagittis pellentesque tortor. In aliquet pretium felis et placerat. Praesent ac massa fringilla arcu condimentum ullamcorper. Morbi ut elit odio, in porttitor sem. Proin semper volutpat sagittis. Morbi at ante ac lacus ultrices hendrerit non ultrices mauris. Suspendisse sapien leo, semper ut cursus vel, vulputate a ligula. Quisque laoreet ornare nibh non varius. Nunc convallis ligula ac lorem lacinia accumsan. Phasellus a elit eget felis hendrerit ullamcorper eu ac enim. Donec nisl dui, tristique in feugiat a, fringilla laoreet nisl. Duis non turpis vitae erat aliquam ornare vitae eu nunc. Fusce ornare quam non est consequat condimentum. Phasellus porta ultrices urna ac euismod.',
	'lorem_ipsum_2' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam vitae accumsan libero. In ac magna ipsum, sed lacinia enim. Suspendisse luctus eleifend cursus. Suspendisse imperdiet facilisis orci, non suscipit orci semper non. Aenean a augue libero, vitae lacinia augue. Aenean vel tincidunt enim. Aenean tristique mauris convallis libero scelerisque viverra. Donec hendrerit magna diam, non vestibulum orci. Fusce quis turpis felis. Nunc vehicula malesuada vestibulum. Aenean eros erat, elementum in tempor id, euismod eget ipsum. Ut sagittis posuere ligula, ut iaculis libero porta at. Donec adipiscing, lectus non vehicula dignissim, augue odio dapibus est, sed porta augue lacus nec tortor. In nulla quam, molestie et convallis ultricies, placerat in magna.\n\nProin et urna a urna aliquam ultricies id ac orci. Morbi vitae leo eget ligula rutrum feugiat placerat pharetra diam. Nam cursus porta libero, nec facilisis dolor pharetra a. Cras placerat suscipit feugiat. Donec sed egestas sapien. Mauris ipsum metus, ullamcorper nec viverra vel, consectetur id massa. Lorem ipsum dolor sit amet, consectetur adipiscing elit. In lacinia, sem a facilisis tempus, libero lacus eleifend est, eget volutpat risus mauris quis metus. Donec mollis imperdiet nunc nec rutrum. Quisque nulla nunc, pharetra at euismod sed, fermentum nec quam. Mauris justo ante, rhoncus quis sollicitudin id, rutrum at magna. Duis sagittis feugiat semper. Morbi commodo nunc et erat mattis pretium. In dapibus, massa ut fermentum commodo, nisl turpis porta ipsum, eget convallis elit metus vel augue.",
	'null_value' => null,
);

$theme->output();

$time_et = microtime(true);

//echo 'Took: ', number_format($time_et - $time_st, 4), ' seconds.';