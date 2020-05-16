<?php

/**
 * MIT License
 *
 * Copyright (c) 2020 Wolf Utz<wpu@hotmail.de>
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

use OmegaCode\JwtSecuredApiCore\Utility\ArrayUtility;
use PHPUnit\Framework\TestCase;

class ArrayUtilityTest extends TestCase
{
    /**
     * @test
     */
    public function convertsSnakeCaseKeys(): void
    {
        $data = [
            'my_test_string' => null,
            'my_otherString' => null,
        ];
        $result = ArrayUtility::snakeCaseKeysToCamelCaseKeys($data);
        $this->assertArrayHasKey('myTestString', $result);
        $this->assertArrayHasKey('myOtherString', $result);
    }

    /**
     * @test
     */
    public function ignoresKebabCaseKeys(): void
    {
        $data = [
            'my-kebab-string' => null,
        ];
        $result = ArrayUtility::snakeCaseKeysToCamelCaseKeys($data);
        $this->assertArrayHasKey('my-kebab-string', $result);
    }

    /**
     * @test
     */
    public function ignoresCamelCaseKeys(): void
    {
        $data = [
            'anOtherString' => null,
        ];
        $result = ArrayUtility::snakeCaseKeysToCamelCaseKeys($data);
        $this->assertArrayHasKey('anOtherString', $result);
    }

    /**
     * @test
     */
    public function convertsPascalCaseKeys(): void
    {
        $data = [
            'MyPascalCaseString' => null,
        ];
        $result = ArrayUtility::snakeCaseKeysToCamelCaseKeys($data);
        $this->assertArrayHasKey('myPascalCaseString', $result);
    }
}
