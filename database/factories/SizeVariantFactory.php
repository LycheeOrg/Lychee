<?php

namespace Database\Factories;

use App\Enum\SizeVariantType;
use App\Models\SizeVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SizeVariant>
 */
class SizeVariantFactory extends Factory
{
    private const h = 360;
    private const w = 540;
    private const fs = 141011;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SizeVariant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hash = fake()->sha1();
        $url = substr($hash, 0, 2) . '/' . substr($hash, 2, 2) . '/' . substr($hash, 4) . '.jpg';

        return ['type' => SizeVariantType::ORIGINAL, 'short_path' => SizeVariantType::ORIGINAL->name() . '/' . $url, 'ratio' => 1.5, 'height' => self::h * 8, 'width' => self::w * 8, 'filesize' => 64 * self::fs];
    }

    /**
     * Creates 7 size variant with correct type and size,
     */
    public function allSizeVariants(): Factory
    {
        $hash = fake()->sha1();
        $url = substr($hash, 0, 2) . '/' . substr($hash, 2, 2) . '/' . substr($hash, 4) . '.jpg';

        return $this->state(new Sequence(
            ['type' => SizeVariantType::ORIGINAL, 'short_path' => SizeVariantType::ORIGINAL->name() . '/' . $url, 'ratio' => 1.5, 'height' => self::h * 8, 'width' => self::w * 8, 'filesize' => 64 * self::fs],
            ['type' => SizeVariantType::MEDIUM2X, 'short_path' => SizeVariantType::MEDIUM2X->name() . '/' . $url, 'ratio' => 1.5, 'height' => self::h * 6, 'width' => self::w * 6, 'filesize' => 36 * self::fs],
            ['type' => SizeVariantType::MEDIUM, 'short_path' => SizeVariantType::MEDIUM->name() . '/' . $url, 'ratio' => 1.5, 'height' => self::h * 3, 'width' => self::w * 3, 'filesize' => 9 * self::fs],
            ['type' => SizeVariantType::SMALL2X, 'short_path' => SizeVariantType::SMALL2X->name() . '/' . $url, 'ratio' => 1.5, 'height' => self::h * 2, 'width' => self::w * 2, 'filesize' => 4 * self::fs],
            ['type' => SizeVariantType::SMALL, 'short_path' => SizeVariantType::SMALL->name() . '/' . $url, 'ratio' => 1.5, 'height' => self::h, 'width' => self::w, 'filesize' => self::fs],
            ['type' => SizeVariantType::THUMB2X, 'short_path' => SizeVariantType::THUMB2X->name() . '/' . $url, 'ratio' => 1.5, 'height' => 400, 'width' => 400, 'filesize' => 160_000],
            ['type' => SizeVariantType::THUMB, 'short_path' => SizeVariantType::THUMB->name() . '/' . $url, 'ratio' => 1.5, 'height' => 200, 'width' => 200, 'filesize' => 40_000],
        ));
    }
}
