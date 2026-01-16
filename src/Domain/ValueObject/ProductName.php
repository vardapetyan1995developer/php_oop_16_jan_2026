<?php

declare(strict_types = 1);

namespace App\Domain\ValueObject;


use InvalidArgumentException;

/**
 * Value object that encapsulates a validated product name.
 */
final readonly class ProductName
{
    private const int MIN_LENGTH = 2;
    private const int MAX_LENGTH = 255;

    private string $value;

    /**
     * Create a ProductName after validating length and emptiness.
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $trimmed = trim($value);

        $this->guardNotEmpty($trimmed);
        $this->guardMinLength($trimmed);
        $this->guardMaxLength($trimmed);

        $this->value = $trimmed;
    }

    /**
     * Build a ProductName from a raw string.
     *
     * @param string $value
     * @return self
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    /**
     * Return the name as a plain string.
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Compare two ProductName instances for equality.
     *
     * @param ProductName $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Check if the name contains a substring (case-insensitive).
     *
     * @param string $substring
     * @return bool
     */
    public function contains(string $substring): bool
    {
        return mb_stripos($this->value, $substring) !== false;
    }

    /**
     * Return the length of the name in characters.
     *
     * @return int
     */
    public function length(): int
    {
        return mb_strlen($this->value);
    }

    /**
     * Ensure the name is not empty.
     *
     * @param string $value
     * @return void
     */
    private function guardNotEmpty(string $value): void
    {
        if ($value === '')
        {
            throw new InvalidArgumentException('Product name cannot be empty');
        }
    }

    /**
     * Ensure the name meets the minimum length.
     *
     * @param string $value
     * @return void
     */
    private function guardMinLength(string $value): void
    {
        if (mb_strlen($value) < self::MIN_LENGTH)
        {
            throw new InvalidArgumentException(
                sprintf('Product name must be at least %d characters, got %d', self::MIN_LENGTH, mb_strlen($value))
            );
        }
    }

    /**
     * Ensure the name does not exceed the maximum length.
     *
     * @param string $value
     * @return void
     */
    private function guardMaxLength(string $value): void
    {
        if (mb_strlen($value) > self::MAX_LENGTH)
        {
            throw new InvalidArgumentException(
                sprintf('Product name must not exceed %d characters, got %d', self::MAX_LENGTH, mb_strlen($value)),
            );
        }
    }

    /**
     * Cast the ProductName to a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
