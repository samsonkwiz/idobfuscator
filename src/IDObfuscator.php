<?php
/**
 * IDObfuscator - A class for securely obfuscating and deobfuscating numeric IDs.
 *
 * This class uses BCMath to handle large numbers and implements a reversible
 * obfuscation algorithm based on XOR, salting, and modular arithmetic.
 *
 * Requirements:
 * - PHP 7.0+ (compatible with PHP 7.0 - 8.2)
 * - BCMath extension
 *
 * @author Your Name
 */
class IDObfuscator {
    /** @var string Secret salt value (<1e10) */
    private $salt;
    /** @var string Secret XOR key (<2^31) */
    private $key;
    /** @var int Length of output code (>=2) */
    private $length;
    /** @var string Multiplier for permutation (must be coprime with modulus) */
    private $multiplier;
    /** @var string Modular inverse of multiplier */
    private $multiplierInverse;
    /** @var string Offset to ensure non-zero leading digit */
    private $offset;
    /** @var string Modulus for modular arithmetic */
    private $modulus;

    public function __construct(
        string $salt = '1357913579',
        string $key  = '987654321',
        int    $length = 11,
        string $multiplier = '1234567',
        ?string $multiplierInv = null
    ) {
        if (!extension_loaded('bcmath')) {
            throw new Exception('BCMath extension is required');
        }
        if (bccomp($salt, '0') < 0 || !ctype_digit($salt)) {
            throw new InvalidArgumentException('Salt must be a non-negative integer string');
        }
        if (bccomp($key, '0') < 0 || !ctype_digit($key)) {
            throw new InvalidArgumentException('Key must be a non-negative integer string');
        }
        if ($length < 2) {
            throw new InvalidArgumentException('Length must be >= 2');
        }
        if (bccomp($multiplier, '1') < 0 || !ctype_digit($multiplier)) {
            throw new InvalidArgumentException('Multiplier must be a positive integer string');
        }

        $this->salt = $salt;
        $this->key  = $key;
        $this->length     = $length;
        $this->multiplier = $multiplier;

        $this->offset  = bcpow('10', (string)($length - 1));
        $this->modulus = bcmul('9', $this->offset);

        if ($multiplierInv === null) {
            $this->multiplierInverse = self::modInverse($multiplier, $this->modulus);
        } else {
            if (!ctype_digit($multiplierInv)) {
                throw new InvalidArgumentException('Multiplier inverse must be numeric string');
            }
            if (bcmod(bcmul($multiplier, $multiplierInv), $this->modulus) !== '1') {
                throw new InvalidArgumentException('Provided multiplier inverse is incorrect');
            }
            $this->multiplierInverse = $multiplierInv;
        }
    }

    public function encode($id): string {
        if (!ctype_digit((string)$id) || bccomp((string)$id, '0') < 0) {
            throw new InvalidArgumentException('ID must be a non-negative integer');
        }
        $id = (string)(int)$id;

        $step1 = $this->bitwiseXor($id, $this->key);
        $step2 = bcadd($step1, $this->salt);
        $perm  = bcmod(bcmul($step2, $this->multiplier), $this->modulus);
        $code  = bcadd($perm, $this->offset);
        return str_pad($code, $this->length, '0', STR_PAD_LEFT);
    }

    public function decode($code): int {
        $digits = preg_replace('/\D/', '', (string)$code);
        if (strlen($digits) < $this->length) {
            throw new InvalidArgumentException('Code must be at least ' . $this->length . ' digits');
        }
        $perm  = bcsub($digits, $this->offset);
        if (bccomp($perm, '0') < 0) {
            throw new InvalidArgumentException('Invalid code');
        }
        $step2 = bcmod(bcmul($perm, $this->multiplierInverse), $this->modulus);
        $step1 = bcsub($step2, $this->salt);
        $orig  = $this->bitwiseXor($step1, $this->key);
        return (int)$orig;
    }

    public static function isBCMathAvailable(): bool {
        return extension_loaded('bcmath');
    }

    private function bitwiseXor(string $a, string $b): string {
        $binA = $this->decimalToBinary($a);
        $binB = $this->decimalToBinary($b);
        $len = max(strlen($binA), strlen($binB));
        $binA = str_pad($binA, $len, '0', STR_PAD_LEFT);
        $binB = str_pad($binB, $len, '0', STR_PAD_LEFT);

        $xor = '';
        for ($i = 0; $i < $len; $i++) {
            $xor .= ($binA[$i] === $binB[$i]) ? '0' : '1';
        }
        return $this->binaryToDecimal($xor);
    }

    private function decimalToBinary(string $dec): string {
        if (!ctype_digit($dec)) {
            throw new InvalidArgumentException('Non-numeric input in decimalToBinary');
        }
        if ($dec === '0') return '0';
        $bin = '';
        while (bccomp($dec, '0') > 0) {
            $bin = bcmod($dec, '2') . $bin;
            $dec = bcdiv($dec, '2', 0);
        }
        return $bin;
    }

    private function binaryToDecimal(string $bin): string {
        if (preg_match('/[^01]/', $bin)) {
            throw new InvalidArgumentException('Invalid binary input');
        }
        $dec = '0';
        for ($i = 0, $len = strlen($bin); $i < $len; $i++) {
            $dec = bcmul($dec, '2');
            if ($bin[$i] === '1') {
                $dec = bcadd($dec, '1');
            }
        }
        return $dec;
    }

    public static function modInverse(string $a, string $m): string {
        $a = bcmod($a, $m);
        $m0 = $m;
        $x0 = '0'; $x1 = '1';
        if (bccomp($m, '1') === 0) return '0';
        while (bccomp($a, '1') > 0) {
            $q    = bcdiv($a, $m, 0);
            $t    = $m;
            $m    = bcmod($a, $m);
            $a    = $t;
            $t    = $x0;
            $x0   = bcsub($x1, bcmul($q, $x0));
            $x1   = $t;
        }
        if (bccomp($x1, '0') < 0) {
            $x1 = bcadd($x1, $m0);
        }
        return $x1;
    }

    public static function obfuscate($id, $salt = '1357913579', $key = '987654321', $length = 11) {
        $o = new self($salt, $key, $length);
        return $o->encode($id);
    }

    public static function deobfuscate($code, $salt = '1357913579', $key = '987654321', $length = 11) {
        $o = new self($salt, $key, $length);
        return $o->decode($code);
    }
}

?>
