<?php

declare(strict_types = 1);

namespace App\Domain\Entity;

use App\Domain\Exception\DomainException;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;
use App\Domain\ValueObject\ProductName;
use DateTimeImmutable;

/**
 * Product aggregate – encapsulates the business logic of a product
 *
 * Invariants:
 * - Quantity >= 0
 * - Price > 0
 * - Name is not empty
 */
final class Product
{
    /**
     * @param ProductId $id
     * @param ProductName $name
     * @param Money $price
     * @param int $quantity
     * @param string|null $description
     * @param DateTimeImmutable $createdAt
     * @param DateTimeImmutable $updatedAt
     */
    public function __construct(
        private readonly ProductId $id,
        private ProductName $name,
        private Money $price,
        private int $quantity,
        private ?string $description,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
        $this->guardQuantity($quantity);
    }

    /**
     * Creates a new product aggregate.
     *
     * Applies all domain invariants and business rules required
     * for creating a product from scratch.
     * @param ProductName $name
     * @param Money $price
     * @param int $quantity
     * @param string|null $description
     * @return self
     */
    public static function create(ProductName $name, Money $price, int $quantity, ?string $description = null): self
    {
        $now = new DateTimeImmutable();

        return new self(
            id: ProductId::generate(),
            name: $name,
            price: $price,
            quantity: $quantity,
            description: self::sanitizeDescription($description),
            createdAt: $now,
            updatedAt: $now,
        );
    }

    /**
     * Reconstitutes a Product aggregate from persistence storage.
     *
     * This named constructor is used to restore an existing product
     * from the database or any other persistent storage. It assumes
     * that all invariants have already been validated at the time
     * the product was originally created.
     *
     * @param ProductId $id
     * @param ProductName $name
     * @param Money $price
     * @param int $quantity
     * @param string|null $description
     * @param DateTimeImmutable $createdAt
     * @param DateTimeImmutable $updatedAt
     * @return self
     */
    public static function fromPersistence(ProductId $id, ProductName $name, Money $price, int $quantity, ?string $description, DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt): self
    {
        return new self(
            id: $id,
            name: $name,
            price: $price,
            quantity: $quantity,
            description: $description,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    /**
     * Updates the product details.
     *
     * Applies domain validation rules and updates the product’s
     * core attributes. The `updatedAt` timestamp is refreshed
     * to reflect the modification time.
     *
     * @param ProductName $name
     * @param Money $price
     * @param int $quantity
     * @param string|null $description
     * @return void
     */
    public function update(ProductName $name, Money $price, int $quantity, ?string $description): void
    {
        $this->guardQuantity($quantity);

        $this->name = $name;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->description = self::sanitizeDescription($description);
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Increases the available product quantity.
     *
     * The amount must be a positive integer. Updates the
     * `updatedAt` timestamp after a successful increase.
     * @param int $amount
     * @return void
     */
    public function increaseQuantity(int $amount): void
    {
        if ($amount <= 0)
        {
            throw DomainException::invalidQuantityIncrease($amount);
        }

        $this->quantity += $amount;
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Decreases the available product quantity.
     *
     * Ensures the decrease amount is positive and that the resulting
     * quantity does not violate domain invariants (e.g. quantity >= 0).
     * Updates the `updatedAt` timestamp after a successful decrease.
     *
     * @param int $amount
     * @return void
     */
    public function decreaseQuantity(int $amount): void
    {
        if ($amount <= 0)
        {
            throw DomainException::invalidQuantityDecrease($amount);
        }

        $newQuantity = $this->quantity - $amount;
        $this->guardQuantity($newQuantity);

        $this->quantity = $newQuantity;
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Determines whether the product is available.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->quantity > 0;
    }

    /**
     * Determines whether the product can fulfill an order
     *
     * @param int $requestedQuantity
     * @return bool
     */
    public function canFulfillOrder(int $requestedQuantity): bool
    {
        return $this->quantity >= $requestedQuantity;
    }

    /**
     * Returns the product identifier.
     *
     * @return ProductId
     */
    public function getId(): ProductId
    {
        return $this->id;
    }

    /**
     * Returns the product name.
     *
     * @return ProductName
     */
    public function getName(): ProductName
    {
        return $this->name;
    }

    /**
     * Returns the product price.
     *
     * @return Money
     */
    public function getPrice(): Money
    {
        return $this->price;
    }

    /**
     * Returns the current available quantity of the product.
     *
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * Returns the product description.
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Returns the date and time when the product was created.
     *
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Returns the date and time when the product was last updated.
     *
     * @return DateTimeImmutable
     */
    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Guards against invalid product quantity values.
     *
     * @param int $quantity
     * @return void
     */
    private function guardQuantity(int $quantity): void
    {
        if ($quantity < 0)
        {
            throw DomainException::negativeQuantity($quantity);
        }
    }

    /**
     * Sanitizes the product description.
     *
     * @param string|null $description
     * @return string|null
     */
    private static function sanitizeDescription(?string $description): ?string
    {
        if ($description === null)
        {
            return null;
        }

        $trimmed = trim($description);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * Returns a string representation of the product.
     *
     * @return string
     */
    public function __toString(): string
    {
        return sprintf('Product{id=%s, name=%s, price=%s}', $this->id->toString(), $this->name->toString(), $this->price->toFloat());
    }
}