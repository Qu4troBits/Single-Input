<?php

declare(strict_types=1);

namespace App\Domain\Security\TwoFactor;

use InvalidArgumentException;

final class Totp
{
    public static function generateSecret(int $bytes = 20): string
    {
        if ($bytes < 10) {
            throw new InvalidArgumentException('Secret length is too small.');
        }

        return self::base32Encode(random_bytes($bytes));
    }

    public static function otpAuthUri(string $issuer, string $accountName, string $secret): string
    {
        $issuer = trim($issuer);
        $accountName = trim($accountName);

        if ($issuer === '' || $accountName === '') {
            throw new InvalidArgumentException('Issuer and account name are required.');
        }

        $label = rawurlencode($issuer.':'.$accountName);

        return sprintf(
            'otpauth://totp/%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            $label,
            rawurlencode($secret),
            rawurlencode($issuer),
        );
    }

    public static function verify(string $secret, string $code, int $window = 1, ?int $timestamp = null): bool
    {
        $code = preg_replace('/\s+/', '', $code) ?? '';

        if (! preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $timestamp = $timestamp ?? time();

        if ($window < 0 || $window > 10) {
            throw new InvalidArgumentException('Invalid TOTP window.');
        }

        $key = self::base32Decode($secret);
        $timeStep = intdiv($timestamp, 30);

        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals(self::generateCode($key, $timeStep + $i), $code)) {
                return true;
            }
        }

        return false;
    }

    private static function generateCode(string $key, int $timeStep): string
    {
        $counter = pack('N*', 0).pack('N*', $timeStep);
        $hash = hash_hmac('sha1', $counter, $key, true);
        $offset = ord($hash[19]) & 0x0f;
        $binary = (ord($hash[$offset]) & 0x7f) << 24;
        $binary |= (ord($hash[$offset + 1]) & 0xff) << 16;
        $binary |= (ord($hash[$offset + 2]) & 0xff) << 8;
        $binary |= (ord($hash[$offset + 3]) & 0xff);
        $otp = $binary % 1000000;

        return str_pad((string) $otp, 6, '0', STR_PAD_LEFT);
    }

    private static function base32Decode(string $value): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $value = strtoupper(preg_replace('/[^A-Z2-7]/i', '', $value) ?? '');

        if ($value === '') {
            throw new InvalidArgumentException('Invalid base32 secret.');
        }

        $bits = '';

        $len = strlen($value);
        for ($i = 0; $i < $len; $i++) {
            $pos = strpos($alphabet, $value[$i]);

            if ($pos === false) {
                throw new InvalidArgumentException('Invalid base32 secret.');
            }

            $bits .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';
        $bitsLen = strlen($bits);

        for ($i = 0; $i + 8 <= $bitsLen; $i += 8) {
            $bytes .= chr(bindec(substr($bits, $i, 8)));
        }

        return $bytes;
    }

    private static function base32Encode(string $bytes): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';
        $len = strlen($bytes);

        for ($i = 0; $i < $len; $i++) {
            $bits .= str_pad(decbin(ord($bytes[$i])), 8, '0', STR_PAD_LEFT);
        }

        $out = '';
        $bitsLen = strlen($bits);

        for ($i = 0; $i < $bitsLen; $i += 5) {
            $chunk = substr($bits, $i, 5);

            if (strlen($chunk) < 5) {
                $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            }

            $out .= $alphabet[bindec($chunk)];
        }

        return $out;
    }
}
