# IDObfuscator for PHP

Secure, reversible numeric ID obfuscation for PHP using BCMath.

IDObfuscator lets you:

- Generate unpredictable numbers for tokens, card numbers, and references.
- Mask your database auto-increment IDs with deterministic, non-sequential values.
- Protect sensitive numeric identifiers in URLs, APIs, and logs.

Thanks to BCMath, it handles arbitrarily large integers and guarantees exact round-trip encoding/decoding.

## Requirements

- PHP 7.0 or higher
- BCMath extension (ext-bcmath)

## Installation

Install via Composer (recommended):

```bash
composer require samsonkwiz/idobfuscator
```

### Other installation methods

**1. Clone the repository and install dependencies**

```bash
git clone https://github.com/samsonkwiz/idobfuscator.git
cd idobfuscator
composer install
```

**2. Download ZIP**

- Go to https://github.com/samsonkwiz/idobfuscator
- Click **"Code" → "Download ZIP"**, extract the archive
- Ensure Composer autoloader is available or include source files manually

**3. Git submodule**

```bash
git submodule add https://github.com/samsonkwiz/idobfuscator.git path/to/IDObfuscator
```

Then update your project's `composer.json` autoload section:

```json
"autoload": {
  "psr-4": {
    "SamsonKwiz\\IDObfuscator\\": "src/"
  }
}
```

Run:
```bash
composer dump-autoload
```

**4. Packagist**

The package is now available on Packagist. Simply run:

```bash
composer require samsonkwiz/idobfuscator
```

## Usage## Usage

1. **Include Composer’s autoloader**

   ```php
   require __DIR__ . '/vendor/autoload.php';
   ```

2. **Import the class**

   ```php
   use SamsonKwiz\IDObfuscator\IDObfuscator;
   ```

3. **Instantiate and encode/decode IDs**

   **A. Using instance methods**

   ```php
   // Create a new obfuscator instance with default settings
   $obfuscator = new IDObfuscator();

   $originalId = 12345;
   $encoded    = $obfuscator->encode($originalId);   // e.g. "004829374"
   $decoded    = $obfuscator->decode($encoded);     // back to 12345

   echo "ID {$originalId} → {$encoded} → {$decoded}";
   ```

   **B. Using static helper methods**

   ```php
   $originalId = 54321;
   $encoded    = IDObfuscator::obfuscate($originalId);
   $decoded    = IDObfuscator::deobfuscate($encoded);

   echo "ID {$originalId} → {$encoded} → {$decoded}";
   ```

4. **Customize salt, key, and output length** (optional)

   ```php
   $salt     = '246802468';
   $key      = '13579135';
   $length   = 12;  // exact length of the obfuscated string

   // Create a custom obfuscator
   $customOb = new IDObfuscator($salt, $key, $length);

   $id       = 98765;
   $encoded  = $customOb->encode($id);
   $decoded  = $customOb->decode($encoded);

   echo "Custom ID {$id} → {$encoded} → {$decoded}";
   ```

## Contributing

Contributions are welcome! Please fork the repository and open a pull request.

## License

This library is released under the MIT License.
