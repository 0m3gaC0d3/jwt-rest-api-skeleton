<?php

/**
 * MIT License
 *
 * Copyright (c) 2021 Wolf Utz<wpu@hotmail.de>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace OmegaCode\JwtSecuredApiCore\Tests\Unit\Utility;

use OmegaCode\JwtSecuredApiCore\Utility\StringUtility;
use PHPUnit\Framework\TestCase;

class StringUtilityTest extends TestCase
{
    /**
     * @test
     */
    public function convertsSnakeCaseToCamelCase(): void
    {
        $data = 'my_test_string';
        $result = StringUtility::snakeCaseToCamelCase($data);
        $this->assertEquals('myTestString', $result);
    }

    /**
     * @test
     */
    public function ignoresKebabCaseToCamelCase(): void
    {
        $data = 'my-test-string';
        $result = StringUtility::snakeCaseToCamelCase($data);
        $this->assertEquals($data, $result);
    }

    /**
     * @test
     */
    public function ignoresCamelCaseToCamelCase(): void
    {
        $data = 'myTestString';
        $result = StringUtility::snakeCaseToCamelCase($data);
        $this->assertEquals($data, $result);
    }

    /**
     * @test
     */
    public function convertsPascalCaseToCamelCase(): void
    {
        $data = 'MyTestString';
        $result = StringUtility::snakeCaseToCamelCase($data);
        $this->assertEquals('myTestString', $result);
    }

    /**
     * @test
     */
    public function convertsCamelCaseToSnakeCase(): void
    {
        $data = 'myTestString';
        $result = StringUtility::camelCaseToSnakeCase($data);
        $this->assertEquals('my_test_string', $result);
    }

    /**
     * @test
     */
    public function ignoresKebabCaseToSnakeCase(): void
    {
        $data = 'my-test-string';
        $result = StringUtility::camelCaseToSnakeCase($data);
        $this->assertEquals($data, $result);
    }

    /**
     * @test
     */
    public function ignoresSnakeCaseToSnakeCase(): void
    {
        $data = 'my_test_string';
        $result = StringUtility::camelCaseToSnakeCase($data);
        $this->assertEquals($data, $result);
    }

    /**
     * @test
     */
    public function convertsPascalCaseToSnakeCase(): void
    {
        $data = 'MyTestString';
        $result = StringUtility::camelCaseToSnakeCase($data);
        $this->assertEquals('my_test_string', $result);
    }
}
