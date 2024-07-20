<?php

declare(strict_types=1);

namespace dev\suvera\exms\utils;


class Utility {

    public const DEFAULT_CIPHER_METHOD = 'AES-256-CBC';

    public static function generateStrongPassword($length = 12): string {
        $allChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-+={}[]|;:,.<>?/~`';

        $randomBytes = random_bytes($length);
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $allChars[ord($randomBytes[$i]) % strlen($allChars)];
        }

        return $password;
    }

    public static function isValidPassword(string $password): bool {
        return preg_match('/^[A-Za-z\d!\@\#\$\\\/\%\^\&\*\(\)_\-\+=\{\}\[\]\|;\:,\.\<\>\?]+$/', $password);
    }

    /**
     * decrypt AES 256
     */
    public static function decrypt(string $edata, string $password): string {
        $data = base64_decode($edata);
        $salt = substr($data, 0, 16);
        $ct = substr($data, 16);

        $rounds = 3; // depends on key length
        $data00 = $password . $salt;
        $hash = array();
        $hash[0] = hash('sha256', $data00, true);
        $result = $hash[0];
        for ($i = 1; $i < $rounds; $i++) {
            $hash[$i] = hash('sha256', $hash[$i - 1] . $data00, true);
            $result .= $hash[$i];
        }
        $ivLen = openssl_cipher_iv_length(self::DEFAULT_CIPHER_METHOD);
        $key = substr($result, 0, 32);
        $iv  = substr($result, 32, $ivLen);

        return openssl_decrypt($ct, self::DEFAULT_CIPHER_METHOD, $key, OPENSSL_RAW_DATA, $iv);
    }

    /**
     * crypt AES 256
     */
    public static function encrypt(string $data, string $password): string {
        $salt = random_bytes(16);
        $salted = '';
        $dx = '';

        // Salt the key(32) and iv(16) = 48
        while (strlen($salted) < 48) {
            $dx = hash('sha256', $dx . $password . $salt, true);
            $salted .= $dx;
        }

        $ivLen = openssl_cipher_iv_length(self::DEFAULT_CIPHER_METHOD);
        $key = substr($salted, 0, 32);
        $iv  = substr($salted, 32, $ivLen);

        $encrypted_data = openssl_encrypt($data, self::DEFAULT_CIPHER_METHOD, $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($salt . $encrypted_data);
    }
}
