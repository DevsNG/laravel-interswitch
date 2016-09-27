<?php

/*
 *
 * Dislcaimer:
 *
 *  This file is part of Unicodeveloper/laravel-paystack package.
 *
 * I am using exact copy of Prosper's TransRef class for
 * TxnRef generation becuase this is currently the most secure way to do it!
 *
 * There's hardly any need to re-invent the wheel. :)
 *
 * (c) Prosper Otemuyiwa <prosperotemuyiwa@gmail.com>
 *
 * Source - http://stackoverflow.com/a/13733588/179104
 *        - https://gist.github.com/raveren/5555297
 *
 */

namespace DevsNG\Interswitch;

class TransRef
{
    /**
     * Get the pool to use based on the type of prefix hash.
     *
     * @param string $type
     *
     * @return string
     */
    private static function getPool($type = 'alnum')
    {
        switch ($type) {
            case 'alnum':
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alpha':
                $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'hexdec':
                $pool = '0123456789abcdef';
                break;
            case 'numeric':
                $pool = '0123456789';
                break;
            case 'nozero':
                $pool = '123456789';
                break;
            case 'distinct':
                $pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
                break;
            default:
                $pool = (string) $type;
                break;
        }

        return $pool;
    }

    /**
     * Generate a random secure crypt figure.
     *
     * @param int $min
     * @param int $max
     *
     * @return int
     */
    private static function secureCrypt($min, $max)
    {
        $range = $max - $min;

        if ($range < 0) {
            return $min; // not so random...
        }

        $log = log($range, 2);
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);

        return $min + $rnd;
    }

    /**
     * Finally, generate a hashed token.
     *
     * @param int $length
     *
     * @return string
     */
    public static function getHashedToken($length = 25)
    {
        $token = '';
        $max = strlen(static::getPool());
        for ($i = 0; $i < $length; ++$i) {
            $token .= static::getPool()[static::secureCrypt(0, $max)];
        }

        return $token;
    }
}
