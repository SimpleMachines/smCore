<?php

/**
 * smCore Test Suite - \smCore\Input
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

namespace smCore;

use PHPUnit_Framework_TestCase;

class InputTest extends PHPUnit_Framework_TestCase
{
	protected $_input;

	public function __construct()
	{
		$this->_input = new Input(array());
	}

	public function testIntegers()
	{
		$this->assertFalse($this->_input->getInt('pi'));
		$this->assertFalse($this->_input->getInt('dodger_blue'));

		$this->assertTrue($this->_input->testInt(5));
		$this->assertTrue($this->_input->testInt('5'));

		$this->assertFalse($this->_input->testInt(5.1));
		$this->assertFalse($this->_input->testInt('five'));
		$this->assertFalse($this->_input->testInt(array()));
	}

	public function testFloats()
	{
		$this->assertTrue($this->_input->testFloat(-1.09552e-11));
		$this->assertTrue($this->_input->testFloat('-1.09552e-11'));
		$this->assertTrue($this->_input->testFloat('+1.09552e-11'));
		$this->assertTrue($this->_input->testFloat('1.09552e-11'));
		$this->assertTrue($this->_input->testFloat('-1.09552E-11'));
		$this->assertTrue($this->_input->testFloat('-1.09552e-11'));
		$this->assertTrue($this->_input->testFloat(3141.59));
		$this->assertTrue($this->_input->testFloat('3,141.59'));

		$this->assertFalse($this->_input->testFloat('pi'));
		$this->assertFalse($this->_input->testFloat('31,41.59'));
		$this->assertFalse($this->_input->testFloat(null));
		$this->assertFalse($this->_input->testFloat('5.0.0'));
	}

	public function testHexes()
	{
		$this->assertTrue($this->_input->testHex(5));
		$this->assertTrue($this->_input->testHex('0123456789abcdefABCDEF'));

		$this->assertFalse($this->_input->getHex('pi'));
		$this->assertFalse($this->_input->testHex('5.0'));
		$this->assertFalse($this->_input->testHex('abcdefgh'));
	}

	public function testPhoneNumbers()
	{
		$this->assertTrue($this->_input->testTelephone('123 4567'));
		$this->assertTrue($this->_input->testTelephone('(123) 456 7890'));
		$this->assertTrue($this->_input->testTelephone('(123) 456-7890'));
		$this->assertTrue($this->_input->testTelephone('123-456-7890'));
		$this->assertTrue($this->_input->testTelephone('123 456 7890'));
		$this->assertTrue($this->_input->testTelephone('1 222 333'));
		$this->assertTrue($this->_input->testTelephone('1 222 333 4444'));
		$this->assertTrue($this->_input->testTelephone('1 (234) 567-8900'));
		$this->assertTrue($this->_input->testTelephone('1-234-567-8900'));
	}

	public function testZipCodes()
	{
		$this->assertTrue($this->_input->testZip('12345'));
		$this->assertTrue($this->_input->testZip('24601'));
		$this->assertTrue($this->_input->testZip('12345-6789'));

		$this->assertFalse($this->_input->testZip('1'), 'Zip code is too short.');
		$this->assertFalse($this->_input->testZip('123456'), 'Zip code is too long.');
		$this->assertFalse($this->_input->testZip('123456789'), 'A dash is required before the "plus 4" portion of the zip code.');
	}

	public function testEmails()
	{
		$this->markTestIncomplete('Email tests have not yet been implemented.');

		// These tests are from Wikipedia's Email Address page
		$this->assertTrue($this->_input->testEmail('niceandsimple@example.com'));
		$this->assertTrue($this->_input->testEmail('very.common@example.com'));
		$this->assertTrue($this->_input->testEmail('a.little.lengthy.but.fine@dept.example.com'));
		$this->assertTrue($this->_input->testEmail('disposable.style.email.with+symbol@example.com'));
		$this->assertTrue($this->_input->testEmail('user@[2001:db8:1ff::a0b:dbd0]'));
		$this->assertTrue($this->_input->testEmail('"much.more unusual"@example.com'));
		$this->assertTrue($this->_input->testEmail('"very.unusual.@.unusual.com"@example.com'));
		$this->assertTrue($this->_input->testEmail('"very.(),:;<>[]\".VERY.\"very@\\ \"very\".unusual"@strange.example.com'));
		$this->assertTrue($this->_input->testEmail('0@a'));
		$this->assertTrue($this->_input->testEmail('postbox@com'));
		$this->assertTrue($this->_input->testEmail('!#$%&\'*+-/=?^_`{}|~@example.org'));
		$this->assertTrue($this->_input->testEmail('"()<>[]:,;@\\\"!#$%&\'*+-/=?^_`{}| ~  ? ^_`{}|~.a"@example.org'));
		$this->assertTrue($this->_input->testEmail('""@example.org'));

		$this->assertFalse($this->_input->testEmail('Abc.example.com'), 'An @ character must separate the local and domain parts');
		$this->assertFalse($this->_input->testEmail('Abc.@example.com'), 'Character dot(.) is last in local part');
		$this->assertFalse($this->_input->testEmail('Abc..123@example.com'), 'Character dot(.) is double');
		$this->assertFalse($this->_input->testEmail('A@b@c@example.com'), 'Only one @ is allowed outside quotation marks');
		$this->assertFalse($this->_input->testEmail('a"b(c)d,e:f;g<h>i[j\k]l@example.com'), 'None of the special characters in this local part is allowed outside quotation marks');
		$this->assertFalse($this->_input->testEmail('just"not"right@example.com'), 'Quoted strings must be dot separated, or the only element making up the local-part');
		$this->assertFalse($this->_input->testEmail('this is"not\allowed@example.com'), 'Spaces, quotes, and backslashes may only exist when within quoted strings and preceded by a slash');
		$this->assertFalse($this->_input->testEmail('this\ still\"not\\allowed@example.com'), 'Even if escaped (preceded by a backslash), spaces, quotes, and backslashes must still be contained by quotes');
	}

	public function testHostnames()
	{
		$this->assertTrue($this->_input->testHostname('192.168.0.1'));
		$this->assertTrue($this->_input->testHostname('0.0.0.0'));
		$this->assertTrue($this->_input->testHostname('255.255.255.255'));
		$this->assertTrue($this->_input->testHostname('12.34.56.78'));
		$this->assertTrue($this->_input->testHostname('::1'), 'localhost');
		$this->assertTrue($this->_input->testHostname('0:0:0:0:0:0:0:1'));
		$this->assertTrue($this->_input->testHostname('2001:4860:4860::8888'), 'Google IPv6 DNS server');

		$this->assertTrue($this->_input->testHostname('google.com'));
		$this->assertTrue($this->_input->testHostname('www.google.com'));
		$this->assertTrue($this->_input->testHostname('localhost'));
		$this->assertTrue($this->_input->testHostname('o.co'));
		$this->assertTrue($this->_input->testHostname('ümlaut'));
		$this->assertTrue($this->_input->testHostname('www.smîle.com'));
		$this->assertTrue($this->_input->testHostname('a.b.c.d.e.f.gh'));
		$this->assertTrue($this->_input->testHostname(str_repeat(str_repeat('A', 63) . '.', 3) . str_repeat('A', 63)));

		$this->assertFalse($this->_input->testHostname(str_repeat(str_repeat('A', 63) . '.', 3) . '.a.' . str_repeat('A', 63)), 'Total length exceeds 255 characters.');
		$this->assertFalse($this->_input->testHostname('www.' . str_repeat('A', 64) . '.com'), 'Part length exceeds 63 characters.');
	}

	public function testIps()
	{
		$this->assertTrue($this->_input->testIp('192.168.0.1'));
		$this->assertTrue($this->_input->testIp('0.0.0.0'));
		$this->assertTrue($this->_input->testIp('255.255.255.255'));
		$this->assertTrue($this->_input->testIp('12.34.56.78'));
		$this->assertTrue($this->_input->testIp('::1'), 'localhost');
		$this->assertTrue($this->_input->testIp('0:0:0:0:0:0:0:1'));
		$this->assertTrue($this->_input->testIp('2001:4860:4860::8888'), 'Google IPv6 DNS server');

		$this->assertFalse($this->_input->testIp('1.1.1.256'));
		$this->assertFalse($this->_input->testIp('0.0.a.0'));
		$this->assertFalse($this->_input->testIp('localhost'));
		$this->assertFalse($this->_input->testIp('google.com'));
		$this->assertFalse($this->_input->testIp('http://google.com'));
		$this->assertFalse($this->_input->testIp('//google.com'));

		$this->assertFalse($this->_input->testIp('0:0:0:0:0:0:1'), 'Expected 8 octets, received 7.');
		$this->assertFalse($this->_input->testIp('1234::5678::9'), 'Only one group of zeros can be collapsed.');
		$this->assertFalse($this->_input->testIp('12345:0:0:0:0:0:0:0', 'First octet is too long.'));
		$this->assertFalse($this->_input->testIp('...'));

		/**
		 * @author Rich Brown <http://forums.intermapper.com/viewtopic.php?t=452>
		 */
		$this->assertTrue($this->_input->testIp('fe80:0000:0000:0000:0204:61ff:fe9d:f156'), 'full form of IPv6');
		$this->assertTrue($this->_input->testIp('fe80:0:0:0:204:61ff:fe9d:f156'), 'drop leading zeroes');
		$this->assertTrue($this->_input->testIp('fe80::204:61ff:fe9d:f156'), 'collapse multiple zeroes to :: in the IPv6 address');
		$this->assertTrue($this->_input->testIp('fe80:0000:0000:0000:0204:61ff:254.157.241.86'), 'IPv4 dotted quad at the end');
		$this->assertTrue($this->_input->testIp('fe80:0:0:0:0204:61ff:254.157.241.86'), 'drop leading zeroes, IPv4 dotted quad at the end');
		$this->assertTrue($this->_input->testIp('fe80::204:61ff:254.157.241.86'), 'dotted quad at the end, multiple zeroes collapsed');
		$this->assertTrue($this->_input->testIp('fe80::'), 'link-local prefix');
		$this->assertTrue($this->_input->testIp('2001::'), 'global unicast prefix');
	}

	public function testIn()
	{
		$this->assertTrue($this->_input->testIn(2, range(1, 3)));
		$this->assertTrue($this->_input->testIn('cat', array('rabbit', 'cat', 'pickle')));
		$this->assertTrue($this->_input->testIn(array('deep'), array('one', 'two', array('deep'))));
		$this->assertTrue($this->_input->testIn('cat', 'I have a cat.'));
		$this->assertTrue($this->_input->testIn(15, '4 8 15 16 23 42'));

		$this->assertFalse($this->_input->testIn(5, range(1, 3)));
		$this->assertFalse($this->_input->testIn('snake', array('rabbit', 'cat', 'pickle')));
		$this->assertFalse($this->_input->testIn('deep', array('one', 'two', array('deep'))));
		$this->assertFalse($this->_input->testIn('cat', 'I have a dog.'));
		$this->assertFalse($this->_input->testIn(108, '4 8 15 16 23 42'));
	}

	public function testGreaterThan()
	{
		$this->assertTrue($this->_input->testGreaterThan(10, 5));
		$this->assertTrue($this->_input->testGreaterThan(0, -5));
		$this->assertTrue($this->_input->testGreaterThan('dog', 'cat'));
		$this->assertTrue($this->_input->testGreaterThan(100, 0x4a));

		$this->assertFalse($this->_input->testGreaterThan(5, 10));
		$this->assertFalse($this->_input->testGreaterThan(5, 5));
		$this->assertFalse($this->_input->testGreaterThan(-5, 0));
		$this->assertFalse($this->_input->testGreaterThan('cat', 'dog'));
		$this->assertFalse($this->_input->testGreaterThan(0x4a, 100));
	}

	public function testLessThan()
	{
		$this->assertTrue($this->_input->testLessThan(5, 10));
		$this->assertTrue($this->_input->testLessThan(-5, 0));
		$this->assertTrue($this->_input->testLessThan('cat', 'dog'));
		$this->assertTrue($this->_input->testLessThan(0x4a, 100));

		$this->assertFalse($this->_input->testLessThan(10, 5));
		$this->assertFalse($this->_input->testLessThan(5, 5));
		$this->assertFalse($this->_input->testLessThan(0, -5));
		$this->assertFalse($this->_input->testLessThan('dog', 'cat'));
		$this->assertFalse($this->_input->testLessThan(100, 0x4a));
	}
}