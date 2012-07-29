<?php

/**
 * smCore Utility Class
 *
 * @package smCore
 * @author smCore Dev Team
 * @license MPL 1.1
 * @version 1.0 Alpha
 *
 * The contents of this file are subject to the Mozilla Public License Version 1.1
 * (the "License"); you may not use this package except in compliance with the
 * License. You may obtain a copy of the License at http://www.mozilla.org/MPL/
 *
 * The Original Code is smCore.
 *
 * The Initial Developer of the Original Code is the smCore project.
 *
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 */

// @todo: get rid of this

namespace smCore;

class Utility
{
	public static function getTimestamp($date, $time = null)
	{
		if (!preg_match('~^(0?[1-9]|1[0-2])([/\.\- ])(0?[1-9]|[12][0-9]|3[01])\2([0-9]{4})$~', $date, $matches))
			return false;

		$month = (int) $matches[1];
		$day = (int) $matches[3];
		$year = (int) $matches[4];

		$hours = 0;
		$minutes = 0;
		$seconds = 0;

		if (null !== $time)
		{
			if (preg_match('~^(1[0-2]|0?[1-9]) ([ap]m)$~', $time, $matches))
			{
				$hours = (int) $matches[1];
				$ampm = $matches[2];
			}
			else if (preg_match('~^(1[0-2]|0?[1-9]):([0-5][0-9]) ([ap]m)$~', $time, $matches))
			{
				$hours = (int) $matches[1];
				$minutes = (int) $matches[2];
				$ampm = $matches[3];
			}
			else if (preg_match('~^(1[0-2]|0?[1-9]):([0-5][0-9]):([0-5][0-9]) ([ap]m)$~', $time, $matches))
			{
				$hours = (int) $matches[1];
				$minutes = (int) $matches[2];
				$seconds = (int) $matches[3];
				$ampm = $matches[4];
			}
			else
			{
				return false;
			}

			if ('pm' === $ampm && 12 !== $hours)
			{
				$hours += 12;
			}
			else if (12 === $hours && 'am' === $ampm)
			{
				$hours = 0;
			}
		}

		if (!checkdate($month, $day, $year))
		{
			return false;
		}

		return mktime($hours, $minutes, $seconds, $month, $day, $year);
	}

	public static function makeDateArray($timestamp)
	{
		return array(
			'month' => date('n', $timestamp),
			'day' => date('j', $timestamp),
			'year' => date('Y', $timestamp),
			'time' => date('g:i A', $timestamp),
			'time_full' => date('g:i:s A', $timestamp),
			'raw' => $timestamp,
		);
	}

	public static function randString($length, $set = 'hex')
	{
		if ($set == 'alphanum')
		{
			$characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
		}
		else if ($set == 'full')
		{
			$characters = 'aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ0123456789!@#$%&*:;';
		}
		else
		{
			$characters = 'abcdef0123456789';
		}

		$string = '';

		for ($i = 0; $i < $length; $i++)
		{
			$string .= $characters[mt_rand(0, strlen($characters) - 1)];
		}

		return $string;
	}
}