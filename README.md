# json-attribute

Map **JSON** (RFC 8259) into **typed PHP objects** and back — attributes, similar ideas to Go’s `encoding/json`.

**Requires PHP 8.0+**

## Features

- **`unmarshal`** — JSON string → DTO (`#[JSONField]`)
- **`marshal`** — DTO → JSON string (mapped fields only; `ignored` omitted)
- **Nested objects, scalar arrays, `RawJson`**, optional **strict unknown keys** (`disallowUnknownFields`)
- **`defaultValue`** when a JSON member is **absent** (non-`null` only)

## Public API

| Method | Role |
|--------|------|
| `JSON::unmarshal(string $json, string $class, bool $disallowUnknownFields = false)` | Parse + hydrate |
| `JSON::marshal(object $instance, int $flags = JSON_THROW_ON_ERROR \| JSON_UNESCAPED_UNICODE)` | Serialize |
| `JSON::embed(string $path)` | Read file (e.g. tests) |

Hydrate from an **array** is **not** public; only full documents via `unmarshal`. Nested hydrate runs **inside** the library.

## Requirements

- **PHP ^8.0**
- Dev: PHPUnit ^9.5, PHPStan ^1.10, PHPCS ^3.7

## Usage

```php
use Nauni\JSON\Attribute\JSONField;
use Nauni\JSON\JSON;

final class Point {
    #[JSONField] public int $x;
    #[JSONField] public int $y;
    #[JSONField(ignored: true)] public string $cache = '';
}

$p = JSON::unmarshal('{"x":1,"y":2}', Point::class);
$json = JSON::marshal($p); // {"x":1,"y":2} — cache not included
```

### `disallowUnknownFields`

If `true`, any JSON key on that object that does not map to a non-ignored `#[JSONField]` causes an error (like Go’s `DisallowUnknownFields`).

### `omitempty` (marshal)

`#[JSONField(omitempty: true)]` skips encoding when the value is null, false, 0, `''`, or `[]`.

### Defaults (unmarshal)

`#[JSONField(defaultValue: false)]` — applied when the **member is missing** from JSON.

## Layout (`src/`)

| Area | Role |
|------|------|
| `JSON` | Facade: `embed`, `unmarshal`, `marshal` |
| `Decode\JsonDecoder` | String → assoc (root = object) |
| `Hydrate\ObjectHydrator` | Array → object (**hydrate**, internal) |
| `Extract\ObjectExtractor` | Object → array tree (**internal**); `JSON::marshal` encodes it |
| `Field\*`, `Json\RawJson`, `Attribute\JSONField` | … |

## Tests

Fixtures: **`tests/testdata/`**

```bash
composer install && vendor/bin/phpunit && vendor/bin/phpstan analyse && vendor/bin/phpcs
```

Docker: **`php:8.0-fpm-alpine`** (`.docker/php/Dockerfile`).

## License

MIT
