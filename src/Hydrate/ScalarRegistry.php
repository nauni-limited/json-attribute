<?php

declare(strict_types=1);

namespace Nauni\JSON\Hydrate;

use Nauni\JSON\Field\FieldBool;
use Nauni\JSON\Field\FieldFloat;
use Nauni\JSON\Field\FieldInt;
use Nauni\JSON\Field\FieldInterface;
use Nauni\JSON\Field\FieldString;

/**
 * Scalar name → FieldInterface (object + list hydrate).
 */
final class ScalarRegistry
{
    /** @var array<string, class-string<FieldInterface>> */
    private const HANDLERS = [
        'string' => FieldString::class,
        'int' => FieldInt::class,
        'float' => FieldFloat::class,
        'bool' => FieldBool::class,
    ];

    /**
     * @return class-string<FieldInterface>|null
     */
    public function handlerFor(string $phpScalarType): ?string
    {
        return self::HANDLERS[$phpScalarType] ?? null;
    }
}
