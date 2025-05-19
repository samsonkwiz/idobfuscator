# IDObfuscator

A PHP library for securely obfuscating and deobfuscating numeric IDs into fixed‑length, unpredictable, numeric strings using BCMath.

**Repository:** [https://github.com/samsonkwiz/idobfuscator](https://github.com/samsonkwiz/idobfuscator) 
**Packagist:** [https://packagist.org/packages/samsonkwiz/idobfuscator](https://packagist.org/packages/samsonkwiz/idobfuscator)

---

## Installation

```bash
composer require samsonkwiz/idobfuscator
```

> Requires PHP >= 7.0 and the BCMath extension.

---

## Usage

### 1. Testing ID Obfuscation Class

```php
echo "<h3>Testing ID Obfuscation Class</h3>";

try {
    $instance = new IDObfuscator();
    for ($i = 149937; $i < 149945; $i++) {
        $encoded = $instance->encode($i);
        $decoded = $instance->decode($encoded);
        printf("ID: %6d → %11s → %6d<br>", $i, $encoded, $decoded);
    }
} catch (Exception $e) {
    echo "<div style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
```

### 2. Using Static Methods

```php
echo "<h4>Using static methods:</h4>";

try {
    for ($i = 149945; $i < 149957; $i++) {
        $encoded = IDObfuscator::obfuscate($i);
        $decoded = IDObfuscator::deobfuscate($encoded);
        printf("ID: %6d → %11s → %6d<br>", $i, $encoded, $decoded);
    }
} catch (Exception $e) {
    echo "<div style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
```

### 3. Using Custom Parameters

```php
echo "<h4>Using custom parameters:</h4>";

try {
    $customSalt = '246802468';
    $customKey  = '13579135';
    $customLen  = 12;
    $customOb   = new IDObfuscator($customSalt, $customKey, $customLen);

    for ($i = 149950; $i < 149957; $i++) {
        $encoded = $customOb->encode($i);
        $decoded = $customOb->decode($encoded);
        printf("ID: %6d → %{$customLen}s → %6d<br>", $i, $encoded, $decoded);
    }
} catch (Exception $e) {
    echo "<div style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
```
