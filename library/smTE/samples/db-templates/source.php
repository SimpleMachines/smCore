<?php

class MySource extends smCore\TemplateEngine\Source
{
	public function __construct($id)
	{
		parent::__construct('', 'databse-template-' . $id);
	}

	public function initialize()
	{
		$this->data_buffer = '';
		parent::initialize();
	}

	public function isDataEOF()
	{
		if (strlen($this->data_buffer) == 0)
		{
			$this->data_buffer = $this->loadData();
			$this->data_pos = 0;
		}

		return parent::isDataEOF();
	}

	protected function readStringToken()
	{
		if (strlen($this->data_buffer) == 0)
		{
			$this->data_buffer = $this->loadData();
			$this->data_pos = 0;
		}

		return parent::readStringToken();
	}

	private function loadData()
	{
		// Or a database query, as the case may be.
		return get_template_data();
	}
}