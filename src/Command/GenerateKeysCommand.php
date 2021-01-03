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

namespace OmegaCode\JwtSecuredApiCore\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateKeysCommand extends Command
{
    protected const CONFIGURATION = [
        'digest_alg' => 'sha512',
        'private_key_bits' => 4096,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ];
    protected static $defaultName = 'api:keys:generate';

    protected function configure(): void
    {
        $this->setDescription('Generates the public and private key pair for JWT.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Generating private and public key using PHP extension ext-openssl...');
        $res = $this->createOpenSSLResource();
        $privateKey = $this->createPrivateKey($res);
        $publicKey = $this->createPublicKey($res);
        $this->createFiles($publicKey, $privateKey);
        $output->writeln('Successfully created key pair!');
        $output->writeln('Public key -> ' . APP_ROOT_PATH . $_ENV['PRIVATE_KEY_PATH']);
        $output->writeln('Private key -> ' . APP_ROOT_PATH . $_ENV['PUBLIC_KEY_PATH']);

        return 0;
    }

    /**
     * @return resource
     */
    protected function createOpenSSLResource()
    {
        $res = openssl_pkey_new(self::CONFIGURATION);
        if ($res === false || !is_resource($res)) {
            throw new Exception('Could not load open ssl resource!');
        }

        return $res;
    }

    /**
     * @param resource $res
     */
    protected function createPrivateKey($res): string
    {
        $privateKey = '';
        openssl_pkey_export($res, $privateKey);

        return $privateKey;
    }

    /**
     * @param resource $res
     */
    protected function createPublicKey($res): string
    {
        $details = openssl_pkey_get_details($res);
        if (!is_array($details) || !isset($details['key']) || empty($details['key'])) {
            throw new Exception('Could not read public key data');
        }

        return $details['key'];
    }

    protected function createFiles(string $publicKey, string $privateKey): void
    {
        file_put_contents(APP_ROOT_PATH . $_ENV['PRIVATE_KEY_PATH'], $privateKey);
        file_put_contents(APP_ROOT_PATH . $_ENV['PUBLIC_KEY_PATH'], $publicKey);
    }
}
