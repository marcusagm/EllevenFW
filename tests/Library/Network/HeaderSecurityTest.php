<?php

/**
 * Elleven Framework
 * Copyright 2017 Marcus Maia <contato@marcusmaia.com>
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.md
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Marcus Maia <contato@marcusmaia.com>
 * @link        http://elleven.marcusmaia.com Elleven Kit
 * @since       1.0.0
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace EllevenFw\Test\Library\Network;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use EllevenFw\Library\Network\HeaderSecurity;

/**
 * Tests for Zend\Diactoros\HeaderSecurity.
 *
 * Tests are largely derived from those for Zend\Http\Header\HeaderValue in
 * Zend Framework, released with the copyright and license below.
 *
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
class HeaderSecurityTest extends TestCase
{

    /**
     * Data for filter value
     */
    public function getFilterValues()
    {
        return [
            ["This is a\n test", "This is a test"],
            ["This is a\r test", "This is a test"],
            ["This is a\n\r test", "This is a test"],
            ["This is a\r\n  test", "This is a\r\n  test"],
            ["This is a \r\ntest", "This is a test"],
            ["This is a \r\n\n test", "This is a  test"],
            ["This is a\n\n test", "This is a test"],
            ["This is a\r\r test", "This is a test"],
            ["This is a \r\r\n test", "This is a \r\n test"],
            ["This is a \r\n\r\ntest", "This is a test"],
            ["This is a \r\n\n\r\n test", "This is a \r\n test"]
        ];
    }

    /**
     * @dataProvider getFilterValues
     */
    public function testFiltersValuesPerRfc7230($value, $expected)
    {
        $this->assertSame($expected, HeaderSecurity::filter($value));
    }

    public function validateValues()
    {
        return [
            ["This is a\n test", 'assertFalse'],
            ["This is a\r test", 'assertFalse'],
            ["This is a\n\r test", 'assertFalse'],
            ["This is a\r\n  test", 'assertTrue'],
            ["This is a \r\ntest", 'assertFalse'],
            ["This is a \r\n\n test", 'assertFalse'],
            ["This is a\n\n test", 'assertFalse'],
            ["This is a\r\r test", 'assertFalse'],
            ["This is a \r\r\n test", 'assertFalse'],
            ["This is a \r\n\r\ntest", 'assertFalse'],
            ["This is a \r\n\n\r\n test", 'assertFalse'],
            ["This is a \xFF test", 'assertFalse'],
            ["This is a \x7F test", 'assertFalse'],
            ["This is a \x7E test", 'assertTrue']
        ];
    }

    /**
     * @dataProvider validateValues
     */
    public function testValidatesValuesPerRfc7230($value, $assertion)
    {
        $this->{$assertion}(HeaderSecurity::isValid($value));
    }

    public function assertInvalidValues()
    {
        return [
            ["This is a\n test"],
            ["This is a\r test"],
            ["This is a\n\r test"],
            ["This is a \r\ntest"],
            ["This is a \r\n\n test"],
            ["This is a\n\n test"],
            ["This is a\r\r test"],
            ["This is a \r\r\n test"],
            ["This is a \r\n\r\ntest"],
            ["This is a \r\n\n\r\n test"],
            [[]]
        ];
    }

    /**
     * @dataProvider assertInvalidValues
     */
    public function testAssertValidRaisesExceptionForInvalidValue($value)
    {
        $this->expectException(\InvalidArgumentException::class);

        HeaderSecurity::assertValid($value);
    }

    public function assertValidValues()
    {
        return [
            ["abc"],
            ["This is a\r\n  test"],
            ["This is a \x7E test"]
        ];
    }

    /**
     * @dataProvider assertValidValues
     */
    public function testAssertValidDoNotExceptionForInvalidValue($value)
    {
        $this->assertTrue(HeaderSecurity::assertValid($value));
    }

    public function invalidHeaderNames()
    {
        return [
            [[]],
            [1],
            [new \stdClass()],
            ['abc def']
        ];
    }

    /**
     * @dataProvider invalidHeaderNames
     */
    public function testAssertValidHeaderNames($value)
    {
        $this->expectException(\InvalidArgumentException::class);

        HeaderSecurity::assertValidName($value);
    }

    public function validHeaderNames()
    {
        return [
            ['header'],
            ['Header'],
            ['x-header'],
            ['X-header-12']
        ];
    }

    /**
     * @dataProvider validHeaderNames
     */
    public function testAssertValidNamesDoNotExceptionForInvalidValue($value)
    {
        $this->assertTrue(HeaderSecurity::assertValidName($value));
    }

}
