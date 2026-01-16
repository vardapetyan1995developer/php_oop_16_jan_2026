<?php

declare(strict_types = 1);

namespace App\Domain\ValueObject;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;


/**
 * Value object that wraps a UUID for product identity.
 */
final readonly class ProductId
{
    private UuidInterface $uuid;

    /**
     * Create a ProductId from a UUID string.
     *
     * @param string $value
     */
    private function __construct(string $value)
    {
        if (!Uuid::isValid($value))
        {
            throw new InvalidArgumentException(
                sprintf('Invalid UUID format: %s', $value),
            );
        }

        $this->uuid = Uuid::fromString($value);
    }

    /**
     * Generate a new ProductId using a random UUID v4.
     *
     * @return self
     */
    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    /**
     * Build a ProductId from a UUID string.
     *
     * @param string $value
     * @return self
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    /**
     * Return the UUID string representation.
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->uuid->toString();
    }

    /**
     * Compare two ProductId instances for equality.
     *
     * @param ProductId $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->uuid);
    }

    /**
     * Cast the ProductId to its UUID string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
