<?php

// Something more should be done with this.

class ToxgTests
{
	protected $passed = 0;
	protected $failed = 0;
	protected $results = array();
	protected $is_cli = null;
	protected $coverage = false;

	public function __construct()
	{
		$this->is_cli = PHP_SAPI === 'cli';

		$this->start_test_coverage();

		require(__DIR__ . '/../include/index.php');
		require(__DIR__ . '/include/index.php');

		$this->run_tests();
	}

	public function output()
	{
		if ($this->is_cli)
			return $this->output_cli();

		echo '<!DOCTYPE html>
<html>
<head>
	<title>', $this->failed > 0 ? '(' . $this->failed . ') ' : '', 'ToxG Tests</title>
	<style type="text/css">
		table {
			border-collapse: collapse;
			}
		td, th {
			border: 1px solid #888;
			padding: 3px 5px;
			}
		th {
			background: #e0e0e0;
			background: -moz-linear-gradient(center top , #ffffff, #e0e0e0);
			}
		.passed {
			background: #d1fcab;
			color: #2d4c06;
			}
		.failed {
			background: #fcbdbd;
			color: #4c0606;
			}
		.center {
			text-align: center;
			}
	</style>
</head>
<body>
	<table>
		<tr>
			<th>Name</th>
			<th>Passed</th>
			<th>Result</th>
			<th>Time</th>
		</tr>';

		$total_time = 0;

		foreach ($this->results as $result)
		{
			echo '
		<tr class="', $result[1] ? 'passed' : 'failed', '">
			<td>', $result[0], '</td>
			<td class="center">', ($result[1] ? 'Yes' : 'No'), '</td>
			<td>', $result[1] ? htmlspecialchars($result[2]) : $result[2], '</td>
			<td>', round($result[3], 5), '</td>
		</tr>';

			$total_time += $result[3];
		}

		echo '
		<tr>
			<th></th>
			<th>', $this->passed, '/', ($this->passed + $this->failed), '</th>
			<th></th>
			<th>', round($total_time, 5), '</th>
	</table>
</body>
</html>';
	}

	protected function output_cli()
	{
		// Loop through results, show only the failures
		foreach ($this->results as $result)
			if (!$result[1])
			{
				echo sprintf('%-60s', $result[0]), "FAILED\n";
				echo '        Reason: ', $result[2], "\n\n";
			}

		echo number_format($this->passed + $this->failed), ' tests run, ', number_format($this->failed), ' failed.', "\n";

		if ($this->coverage)
			echo 'Coverage: ', number_format(100 * $this->get_test_coverage()), '%', "\n";

		if ($this->failed == 0)
			exit(0);
		else
			exit(1);
	}

	protected function run_tests()
	{
		global $argv;

		$list = new ToxgTestList();
		$list->loadFrom(__DIR__);

		if (isset($argv))
			$only_tests = array_diff(array_slice($argv, 1), (array) '--coverage');
		else
			$only_tests = array();

		$reason = null;
		$t = null;

		$funcs = $list->getTestFuncs();

		foreach ($funcs as $func)
		{
			$name = substr($func, strlen('test_'));

			// !!! Do something more complicated like a wildcard?
			if (!empty($only_tests) && !in_array($name, $only_tests))
				continue;

			// !!! Should do something with timings ($t.)
			if ($list->executeTest($func, $reason, $t))
			{
				$this->results[] = array($name, true, $reason, $t);
				$this->passed++;
			}
			else
			{
				$this->results[] = array($name, false, $reason, $t);
				$this->failed++;
			}
		}
	}

	protected function start_test_coverage()
	{
		global $argv;

		if (!isset($argv) || !in_array('--coverage', $argv) || !function_exists('xdebug_start_code_coverage'))
			return;

		$this->coverage = true;

		xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
	}

	function get_test_coverage()
	{
		$coverage_data = xdebug_get_code_coverage();

		$covered = 0;
		$uncovered = 0;

		foreach ($coverage_data as $filename => $lines)
		{
			if (strpos(basename($filename), '.test.output') === 0 || strpos($filename, DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR) !== false)
				unset($coverage_data[$filename]);
		}

		ksort($coverage_data);

		$f_report = @fopen(__DIR__ . '/.test.coverage', 'wt');

		foreach ($coverage_data as $filename => $lines)
		{
			foreach ($lines as $line => $state)
			{
				if ($state > 0)
					$covered++;
				elseif ($this->check_uncovered_line($filename, $line, $state == -2 ? 'dead' : 'uncovered'))
				{
					$uncovered++;
					$this->report_uncovered_line($f_report, $filename, $line, $state == -2 ? 'dead' : 'uncovered');
				}
			}
		}

		fclose($f_report);

		if ($covered + $uncovered > 0)
			return $covered / ($covered + $uncovered);
		else
			return 0;
	}

	protected function check_uncovered_line($filename, $line, $state)
	{
		// In many cases, it says dead, but it's not really code at all.
		if ($state === 'dead')
		{
			$source = file($filename);

			// Check for } after a return, assert, throw, or break.
			if ($line > 1 && trim($source[$line - 1]) === '}' && substr_count($source[$line - 2], ';') === 1)
			{
				$prev_line = trim($source[$line - 2]);
				if (strpos($prev_line, 'return') === 0 || strpos($prev_line, 'throw') === 0 || strpos($prev_line, 'break') === 0)
					return false;
				// This can't be passed.
				elseif ($prev_line === 'assert (false);')
					return false;
			}
		}

		return true;
	}

	protected function report_uncovered_line($f_report, $filename, $line, $state)
	{
		if (!$f_report)
			return;

		fwrite($f_report, $filename . ':' . $line . ($state === 'dead' ? ' (DEAD)' : '') . "\n");
	}
}

$tests = new ToxgTests();
$tests->output();