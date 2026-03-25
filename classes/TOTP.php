<?php
class TOTP {
    private $secret;
    private $digits = 6;
    private $period = 30;
    private $algorithm = 'sha1';

    public function __construct($secret = null) {
        $this->secret = $secret ?? $this->generateSecret();
    }

    public static function generateSecret($length = 16) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $secret;
    }

    public function getSecret() {
        return $this->secret;
    }

    public function generateCode($time = null) {
        if ($time === null) {
            $time = time();
        }
        
        $time = floor($time / $this->period);
        
        $secretKey = $this->base32Decode($this->secret);
        
        $timeHex = str_pad(dechex($time), 16, '0', STR_PAD_LEFT);
        $timeBin = '';
        for ($i = 0; $i < strlen($timeHex); $i += 2) {
            $timeBin .= chr(hexdec($timeHex[$i] . $timeHex[$i + 1]));
        }
        
        $hash = hash_hmac($this->algorithm, $timeBin, $secretKey, true);
        
        $offset = ord(substr($hash, -1)) & 0x0F;
        
        $binary = '';
        for ($i = 0; $i < 4; $i++) {
            $binary .= $hash[$offset + $i];
        }
        
        $unpacked = unpack('N', $binary);
        $truncate = ($unpacked[1] & 0x7FFFFFFF) % pow(10, $this->digits);
        
        return str_pad($truncate, $this->digits, '0', STR_PAD_LEFT);
    }

    public function verifyCode($code, $window = 1) {
        $time = time();
        
        for ($i = -$window; $i <= $window; $i++) {
            $t = $time + ($i * $this->period);
            $c = $this->generateCode($t);
            if ($c === (string)$code) {
                return true;
            }
        }
        
        return false;
    }

    private function base32Decode($base32) {
        $base32 = strtoupper($base32);
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        
        $output = '';
        $buffer = 0;
        $bitsLeft = 0;
        
        for ($i = 0; $i < strlen($base32); $i++) {
            $char = $base32[$i];
            $value = strpos($chars, $char);
            if ($value === false) continue;
            
            $buffer = ($buffer << 5) | $value;
            $bitsLeft += 5;
            
            while ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }
        
        if ($bitsLeft > 0) {
            $output .= chr(($buffer << (8 - $bitsLeft)) & 0xFF);
        }
        
        return $output;
    }
}