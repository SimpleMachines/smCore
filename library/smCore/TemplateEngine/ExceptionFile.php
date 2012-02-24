<?php

namespace smCore\TemplateEngine;

class ExceptionFile extends Exception
{
	public function __construct($file, $line, $id_message)
	{
		$this->tpl_file = $file;
		$this->tpl_line = $line;

		$params = func_get_args();
		$params = array_slice($params, 3);

		parent::__construct($id_message, $params);
	}
}