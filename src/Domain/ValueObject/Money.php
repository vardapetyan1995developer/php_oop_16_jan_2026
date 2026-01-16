<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use InvalidArgumentException;


/**
 * Value object for money amounts with currency validation.
 */
final readonly class Money
{
    private const int SCALE = 100;
    private const array ALLOWED_CURRENCIES = ['RUB', 'USD', 'EUR'];

    private int $amount;
    private string $currency;

    /**
     * Create a Money instance from minor units and currency.
     *
     * @param int $amount
     * @param string $currency
     */
    private function __construct(int $amount, string $currency)
    {
        $this->guardNonNegative($amount);
        $this->guardValidCurrency($currency);

        $this->amount = $amount;
        $this->currency = strtoupper($currency);
    }

    /**
     * Build Money from a float amount in major units.
     *
     * @param float $amount
     * @param string $currency
     * @return self
     */
    public static function fromFloat(float $amount, string $currency = 'USD'): self
    {
        if ($amount < 0)
        {
            throw new InvalidArgumentException('Amount cannot be negative');
        }

        $minorUnits = (int) round($amount * self::SCALE);

        return new self($minorUnits, $currency);
    }

    /**
     * Build Money from an integer amount in minor units.
     *
     * @param int $minorUnits
     * @param string $currency
     * @return self
     */
    public static function fromMinorUnits(int $minorUnits, string $currency = 'USD'): self
    {
        return new self($minorUnits, $currency);
    }

    /**
     * Create a zero-valued Money for the given currency.
     *
     * @param string $currency
     * @return self
     */
    public static function zero(string $currency = 'USD'): self
    {
        return new self(0, $currency);
    }

    /**
     * Return the amount as a float in major units.
     *
     * @return float
     */
    public function toFloat(): float
    {
        return $this->amount / self::SCALE;
    }

    /**
     * Return the amount in minor units.
     *
     * @return int
     */
    public function toMinorUnits(): int
    {
        return $this->amount;
    }

    /**
     * Return the currency code.
     *
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Add another Money amount with the same currency.
     *
     * @param Money $other
     * @return self
     */
    public function add(self $other): self
    {
        $this->guardSameCurrency($other);

        return new self($this->amount + $other->amount, $this->currency);
    }

    /**
     * Subtract another Money amount with the same currency.
     *
     * @param Money $other
     * @return self
     */
    public function subtract(self $other): self
    {
        $this->guardSameCurrency($other);

        $newAmount = $this->amount - $other->amount;

        if ($newAmount < 0)
        {
            throw new InvalidArgumentException('Subtraction would result in negative amount');
        }

        return new self($newAmount, $this->currency);
    }

    /**
     * Multiply the amount by a non-negative multiplier.
     *
     * @param int|float $multiplier
     * @return self
     */
    public function multiply(int|float $multiplier): self
    {
        if ($multiplier < 0)
        {
            throw new InvalidArgumentException('Multiplier cannot be negative');
        }

        $newAmount = (int) round($this->amount * $multiplier);

        return new self($newAmount, $this->currency);
    }

    /**
     * Compare two Money instances for equality.
     *
     * @param Money $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    /**
     * Check if this amount is greater than another.
     *
     * @param Money $other
     * @return bool
     */
    public function greaterThan(self $other): bool
    {
        $this->guardSameCurrency($other);
        return $this->amount > $other->amount;
    }

    /**
     * Check if this amount is less than another.
     *
     * @param Money $other
     * @return bool
     */
    public function lessThan(self $other): bool
    {
        $this->guardSameCurrency($other);
        return $this->amount < $other->amount;
    }

    /**
     * Check if the amount is zero.
     *
     * @return bool
     */
    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    /**
     * Check if the amount is positive.
     *
     * @return bool
     */
    public function isPositive(): bool
    {
        return $this->amount > 0;
    }

    /**
     * Format the amount with two decimals and currency.
     *
     * @return string
     */
    public function format(): string
    {
        return sprintf('%.2f %s', $this->toFloat(), $this->currency);
    }

    /**
     * Ensure the amount is not negative.
     *
     * @param int $amount
     * @return void
     */
    private function guardNonNegative(int $amount): void
    {
        if ($amount < 0)
        {
            throw new InvalidArgumentException('Amount cannot be negative');
        }
    }

    /**
     * Ensure the currency is in the allowed list.
     *
     * @param string $currency
     * @return void
     */
    private function guardValidCurrency(string $currency): void
    {
        $upper = strtoupper($currency);

        if (!in_array($upper, self::ALLOWED_CURRENCIES, true))
        {
            throw new InvalidArgumentException(
                sprintf('Invalid currency: %s. Allowed: %s', $currency, implode(', ', self::ALLOWED_CURRENCIES)),
            );
        }
    }

    /**
     * Ensure both Money instances have the same currency.
     *
     * @param Money $other
     * @return void
     */
    private function guardSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency)
        {
            throw new InvalidArgumentException(
                sprintf('Cannot operate on different currencies: %s and %s', $this->currency, $other->currency),
            );
        }
    }

    /**
     * Cast to a formatted string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->format();
    }
}