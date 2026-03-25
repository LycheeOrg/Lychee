<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Database\Factories;

use App\Enum\PhotoWebhookEvent;
use App\Enum\WebhookMethod;
use App\Enum\WebhookPayloadFormat;
use App\Models\Webhook;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Webhook>
 */
class WebhookFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var class-string<Webhook>
	 */
	protected $model = Webhook::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
			'name' => $this->faker->words(3, true),
			'event' => $this->faker->randomElement(PhotoWebhookEvent::cases()),
			'method' => $this->faker->randomElement(WebhookMethod::cases()),
			'url' => $this->faker->url(),
			'payload_format' => $this->faker->randomElement(WebhookPayloadFormat::cases()),
			'secret' => null,
			'secret_header' => null,
			'enabled' => true,
			'send_photo_id' => true,
			'send_album_id' => true,
			'send_title' => true,
			'send_size_variants' => false,
			'size_variant_types' => null,
		];
	}

	/**
	 * State: webhook is disabled.
	 */
	public function disabled(): static
	{
		return $this->state(fn (array $attributes) => ['enabled' => false]);
	}

	/**
	 * State: webhook has a secret configured.
	 */
	public function withSecret(string $secret = 'test-secret', string $header = 'X-Webhook-Secret'): static
	{
		return $this->state(fn (array $attributes) => [
			'secret' => $secret,
			'secret_header' => $header,
		]);
	}

	/**
	 * State: webhook delivers payload as JSON body.
	 */
	public function jsonFormat(): static
	{
		return $this->state(fn (array $attributes) => ['payload_format' => WebhookPayloadFormat::JSON]);
	}

	/**
	 * State: webhook delivers payload as query string.
	 */
	public function queryStringFormat(): static
	{
		return $this->state(fn (array $attributes) => ['payload_format' => WebhookPayloadFormat::QUERY_STRING]);
	}

	/**
	 * State: webhook subscribes to photo.add event via POST.
	 */
	public function onPhotoAdd(): static
	{
		return $this->state(fn (array $attributes) => [
			'event' => PhotoWebhookEvent::ADD,
			'method' => WebhookMethod::POST,
		]);
	}

	/**
	 * State: webhook subscribes to photo.move event via POST.
	 */
	public function onPhotoMove(): static
	{
		return $this->state(fn (array $attributes) => [
			'event' => PhotoWebhookEvent::MOVE,
			'method' => WebhookMethod::POST,
		]);
	}

	/**
	 * State: webhook subscribes to photo.delete event via POST.
	 */
	public function onPhotoDelete(): static
	{
		return $this->state(fn (array $attributes) => [
			'event' => PhotoWebhookEvent::DELETE,
			'method' => WebhookMethod::POST,
		]);
	}
}
