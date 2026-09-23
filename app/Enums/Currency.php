<?php

namespace App\Enums;

enum Currency: string
{
    case Usd = 'USD';
    case Ghs = 'GHS';
    case Eur = 'EUR';
    case Gbp = 'GBP';
    case Ngn = 'NGN';
    case Kes = 'KES';
    case Zar = 'ZAR';
    case Cad = 'CAD';
    case Xof = 'XOF';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function symbol(): string
    {
        return match ($this) {
            self::Usd => '$',
            self::Ghs => 'GH₵',
            self::Eur => '€',
            self::Gbp => '£',
            self::Ngn => '₦',
            self::Kes => 'KSh',
            self::Zar => 'R',
            self::Cad => 'CA$',
            self::Xof => 'CFA',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Usd => 'US Dollar (USD)',
            self::Ghs => 'Ghana Cedi (GHS)',
            self::Eur => 'Euro (EUR)',
            self::Gbp => 'British Pound (GBP)',
            self::Ngn => 'Nigerian Naira (NGN)',
            self::Kes => 'Kenyan Shilling (KES)',
            self::Zar => 'South African Rand (ZAR)',
            self::Cad => 'Canadian Dollar (CAD)',
            self::Xof => 'West African CFA Franc (XOF)',
        };
    }

    /**
     * @return array{code: string, symbol: string}
     */
    public static function shared(?self $currency): array
    {
        $currency ??= self::Usd;

        return [
            'code' => $currency->value,
            'symbol' => $currency->symbol(),
        ];
    }

    /**
     * @return array{code: string, symbol: string}
     */
    public static function resolve(?self $project, ?self $workspace): array
    {
        return self::shared($project ?? $workspace);
    }

    /**
     * @return list<array{code: string, symbol: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $currency): array => [
            'code' => $currency->value,
            'symbol' => $currency->symbol(),
            'label' => $currency->label(),
        ], self::cases());
    }
}
