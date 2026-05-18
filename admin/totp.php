<?php
// admin/totp.php - TOTP (Time-based One-Time Password) implementation per RFC 6238

class TOTP {
    private string $secret;
    private int $digits;
    private int $period;

    public function __construct(string $secret, int $digits = 6, int $period = 30) {
        $this->secret = $secret;
        $this->digits = $digits;
        $this->period = $period;
    }

    public static function generateSecret(int $length = 20): string {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        $bytes = random_bytes($length);
        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[ord($bytes[$i]) % 32];
        }
        return $secret;
    }

    public function getCode(int $time = null): string {
        if ($time === null) $time = time();
        $counter = floor($time / $this->period);
        $counterBinary = pack('N*', 0) . pack('N*', (int)$counter);
        $hash = hash_hmac('sha1', $counterBinary, $this->base32Decode($this->secret), true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $code = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        ) % pow(10, $this->digits);
        return str_pad((string)$code, $this->digits, '0', STR_PAD_LEFT);
    }

    public function verify(string $code, int $tolerance = 1): bool {
        $time = time();
        for ($i = -$tolerance; $i <= $tolerance; $i++) {
            if (hash_equals($this->getCode($time + $i * $this->period), $code)) {
                return true;
            }
        }
        return false;
    }

    public function getProvisioningUri(string $issuer = 'StaticPorto', string $account = 'admin'): string {
        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&digits=%d&period=%d',
            rawurlencode($issuer),
            rawurlencode($account),
            $this->secret,
            rawurlencode($issuer),
            $this->digits,
            $this->period
        );
    }

    private function base32Decode(string $input): string {
        $map = array_flip(str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'));
        $input = strtoupper(rtrim($input, '='));
        $binary = '';
        foreach (str_split($input) as $char) {
            if (!isset($map[$char])) continue;
            $binary .= str_pad(decbin($map[$char]), 5, '0', STR_PAD_LEFT);
        }
        $output = '';
        for ($i = 0; $i + 8 <= strlen($binary); $i += 8) {
            $output .= chr(bindec(substr($binary, $i, 8)));
        }
        return $output;
    }
}
