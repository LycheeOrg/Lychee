<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\Image\Handlers;

use App\DTO\ImageDimension;
use App\Enum\ShiftType;
use App\Enum\ShiftX;
use App\Enum\ShiftY;
use App\Enum\WatermarkPosition;
use App\Image\CoordinateCalculator;
use App\Models\Configs;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;
use Tests\Traits\RequireSE;

class CoordinateCalculatorUnitTest extends AbstractTestCase
{
	use RequireSE;
	use DatabaseTransactions;

	private CoordinateCalculator $calculator;

	protected function setUp(): void
	{
		parent::setUp();
		$this->requireSe();
		$this->calculator = resolve(CoordinateCalculator::class);
	}

	protected function tearDown(): void
	{
		$this->resetSe();
		parent::tearDown();
	}

	/**
	 * Test that to_percent method converts integers to correct percentage values.
	 */
	public function testToPercent(): void
	{
		// Test normal values
		self::assertEquals(0.5, $this->calculator->to_percent(50));
		self::assertEquals(0.01, $this->calculator->to_percent(1));
		self::assertEquals(1.0, $this->calculator->to_percent(100));

		// Test boundary enforcement
		self::assertEquals(0.0, $this->calculator->to_percent(-10)); // Below min
		self::assertEquals(1.0, $this->calculator->to_percent(150)); // Above max

		// Test with custom min/max
		self::assertEquals(0.1, $this->calculator->to_percent(10, 10, 90)); // At min
		self::assertEquals(0.9, $this->calculator->to_percent(90, 10, 90)); // At max
		self::assertEquals(0.1, $this->calculator->to_percent(5, 10, 90));  // Below min
		self::assertEquals(0.9, $this->calculator->to_percent(95, 10, 90)); // Above max
	}

	/**
	 * Test that apply_scaling correctly scales watermark dimensions.
	 */
	public function testApplyScaling(): void
	{
		$originalDimension = new ImageDimension(width: 200, height: 100);

		// Test with 50% size
		Configs::set('watermark_size', 50);
		$scaledDimension = $this->calculator->apply_scaling($originalDimension);
		self::assertEquals(100, $scaledDimension->width);
		self::assertEquals(50, $scaledDimension->height);

		// Test with 25% size
		Configs::set('watermark_size', 25);
		$scaledDimension = $this->calculator->apply_scaling($originalDimension);
		self::assertEquals(50, $scaledDimension->width);
		self::assertEquals(25, $scaledDimension->height);

		// Test with 100% size
		Configs::set('watermark_size', 100);
		$scaledDimension = $this->calculator->apply_scaling($originalDimension);
		self::assertEquals(200, $scaledDimension->width);
		self::assertEquals(100, $scaledDimension->height);

		// Test with minimum allowed value
		Configs::set('watermark_size', 1);
		$scaledDimension = $this->calculator->apply_scaling($originalDimension);
		self::assertEquals(2, $scaledDimension->width);  // 1% of 200
		self::assertEquals(1, $scaledDimension->height); // 1% of 100

		Configs::set('watermark_size', 200); // Above max
		$scaledDimension = $this->calculator->apply_scaling($originalDimension);
		self::assertEquals(200, $scaledDimension->width); // Should be capped at 100%
		self::assertEquals(100, $scaledDimension->height);
	}

	/**
	 * Test that get_opacity correctly returns the opacity value.
	 */
	public function testGetOpacity(): void
	{
		// Test with 80% opacity
		Configs::set('watermark_opacity', 80);
		self::assertEquals(0.8, $this->calculator->get_opacity());

		// Test with minimum value (1% opacity)
		Configs::set('watermark_opacity', 1);
		self::assertEquals(0.01, $this->calculator->get_opacity());

		// Test with 100% opacity
		Configs::set('watermark_opacity', 100);
		self::assertEquals(1.0, $this->calculator->get_opacity());

		// Test with out-of-bounds value above max
		Configs::set('watermark_opacity', 150);
		self::assertEquals(1.0, $this->calculator->get_opacity()); // Should be capped at 1.0
	}

	/**
	 * Test that get_coordinates returns correct positions for different placement options.
	 */
	public function testGetCoordinates(): void
	{
		$imgDimensions = new ImageDimension(width: 1000, height: 800);
		$watermarkDimensions = new ImageDimension(width: 200, height: 100);

		// Test TOP_LEFT position
		Configs::set('watermark_position', WatermarkPosition::TOP_LEFT->value);
		$coords = $this->calculator->get_coordinates($imgDimensions, $watermarkDimensions);
		self::assertEquals(0, $coords->width);
		self::assertEquals(0, $coords->height);

		// Test CENTER position
		Configs::set('watermark_position', WatermarkPosition::CENTER->value);
		$coords = $this->calculator->get_coordinates($imgDimensions, $watermarkDimensions);
		self::assertEquals(400, $coords->width);  // (1000 - 200) / 2
		self::assertEquals(350, $coords->height); // (800 - 100) / 2

		// Test BOTTOM_RIGHT position
		Configs::set('watermark_position', WatermarkPosition::BOTTOM_RIGHT->value);
		$coords = $this->calculator->get_coordinates($imgDimensions, $watermarkDimensions);
		self::assertEquals(800, $coords->width);  // 1000 - 200
		self::assertEquals(700, $coords->height); // 800 - 100

		// Test TOP position (horizontally centered, top aligned)
		Configs::set('watermark_position', WatermarkPosition::TOP->value);
		$coords = $this->calculator->get_coordinates($imgDimensions, $watermarkDimensions);
		self::assertEquals(400, $coords->width);  // (1000 - 200) / 2
		self::assertEquals(0, $coords->height);   // Top aligned

		// Test LEFT position (left aligned, vertically centered)
		Configs::set('watermark_position', WatermarkPosition::LEFT->value);
		$coords = $this->calculator->get_coordinates($imgDimensions, $watermarkDimensions);
		self::assertEquals(0, $coords->width);    // Left aligned
		self::assertEquals(350, $coords->height); // (800 - 100) / 2
	}

	/**
	 * Test apply_shift with absolute positioning.
	 */
	public function testApplyShiftAbsolute(): void
	{
		$imgDimensions = new ImageDimension(width: 1000, height: 800);
		$coordinates = new ImageDimension(width: 400, height: 300);

		// Set up for absolute shift
		Configs::set('watermark_shift_type', ShiftType::ABSOLUTE->value);

		// Test RIGHT shift by 50px
		Configs::set('watermark_shift_x_direction', ShiftX::RIGHT->value);
		Configs::set('watermark_shift_x', 50);
		Configs::set('watermark_shift_y_direction', ShiftY::DOWN->value);
		Configs::set('watermark_shift_y', 0); // No vertical shift

		$shifted = $this->calculator->apply_shift($imgDimensions, $coordinates);
		self::assertEquals(450, $shifted->width);  // 400 + 50
		self::assertEquals(300, $shifted->height); // 300 + 0

		// Test LEFT and UP shifts
		Configs::set('watermark_shift_x_direction', ShiftX::LEFT->value);
		Configs::set('watermark_shift_x', 100);
		Configs::set('watermark_shift_y_direction', ShiftY::UP->value);
		Configs::set('watermark_shift_y', 50);

		$shifted = $this->calculator->apply_shift($imgDimensions, $coordinates);
		self::assertEquals(300, $shifted->width);  // 400 - 100
		self::assertEquals(250, $shifted->height); // 300 - 50
	}

	/**
	 * Test apply_shift with relative positioning.
	 */
	public function testApplyShiftRelative(): void
	{
		$imgDimensions = new ImageDimension(width: 1000, height: 800);
		$coordinates = new ImageDimension(width: 400, height: 300);

		// Set up for relative shift
		Configs::set('watermark_shift_type', ShiftType::RELATIVE->value);

		// Test RIGHT shift by 10% of image width
		Configs::set('watermark_shift_x_direction', ShiftX::RIGHT->value);
		Configs::set('watermark_shift_x', 10); // 10% = 100px
		Configs::set('watermark_shift_y_direction', ShiftY::DOWN->value);
		Configs::set('watermark_shift_y', 5);  // 5% = 40px

		$shifted = $this->calculator->apply_shift($imgDimensions, $coordinates);
		self::assertEquals(500, $shifted->width);  // 400 + (10% of 1000)
		self::assertEquals(340, $shifted->height); // 300 + (5% of 800)

		// Test LEFT and UP shifts with percentage
		Configs::set('watermark_shift_x_direction', ShiftX::LEFT->value);
		Configs::set('watermark_shift_x', 20); // 20% = 200px
		Configs::set('watermark_shift_y_direction', ShiftY::UP->value);
		Configs::set('watermark_shift_y', 10); // 10% = 80px

		$shifted = $this->calculator->apply_shift($imgDimensions, $coordinates);
		self::assertEquals(200, $shifted->width);  // 400 - (20% of 1000)
		self::assertEquals(220, $shifted->height); // 300 - (10% of 800)
	}

	/**
	 * Test that apply_shift respects image boundaries.
	 */
	public function testApplyShiftBoundaries(): void
	{
		$imgDimensions = new ImageDimension(width: 1000, height: 800);
		$coordinates = new ImageDimension(width: 50, height: 50);

		// Set up for absolute shift
		Configs::set('watermark_shift_type', ShiftType::ABSOLUTE->value);

		// Test shift that would move watermark outside left boundary
		Configs::set('watermark_shift_x_direction', ShiftX::LEFT->value);
		Configs::set('watermark_shift_x', 100); // More than the current position
		Configs::set('watermark_shift_y_direction', ShiftY::DOWN->value);
		Configs::set('watermark_shift_y', 0);

		$shifted = $this->calculator->apply_shift($imgDimensions, $coordinates);
		self::assertEquals(0, $shifted->width); // Should be clamped to 0 (left edge)
		self::assertEquals(50, $shifted->height);

		// Test shift that would move watermark outside right boundary
		Configs::set('watermark_shift_x_direction', ShiftX::RIGHT->value);
		Configs::set('watermark_shift_x', 1000); // More than image width

		$shifted = $this->calculator->apply_shift($imgDimensions, $coordinates);
		self::assertEquals(1000, $shifted->width); // Should be clamped to image width
		self::assertEquals(50, $shifted->height);

		// Test shift that would move watermark outside top boundary
		Configs::set('watermark_shift_x_direction', ShiftX::RIGHT->value);
		Configs::set('watermark_shift_x', 0);
		Configs::set('watermark_shift_y_direction', ShiftY::UP->value);
		Configs::set('watermark_shift_y', 100); // More than current position

		$shifted = $this->calculator->apply_shift($imgDimensions, $coordinates);
		self::assertEquals(50, $shifted->width);
		self::assertEquals(0, $shifted->height); // Should be clamped to 0 (top edge)

		// Test shift that would move watermark outside bottom boundary
		Configs::set('watermark_shift_y_direction', ShiftY::DOWN->value);
		Configs::set('watermark_shift_y', 1000); // More than image height

		$shifted = $this->calculator->apply_shift($imgDimensions, $coordinates);
		self::assertEquals(50, $shifted->width);
		self::assertEquals(800, $shifted->height); // Should be clamped to image height
	}
}