<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models;

use App\Enum\ConfigType;
use App\Enum\LicenseType;
use App\Enum\MapProviders;
use App\Exceptions\Internal\InvalidConfigOption;
use App\Exceptions\Internal\LycheeAssertionError;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\ModelDBException;
use App\Models\Builders\ConfigsBuilder;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Repositories\ConfigManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * App\Models\Configs.
 *
 * @property int         $id
 * @property string      $key
 * @property string|null $value
 * @property string      $cat
 * @property string      $type_range
 * @property bool        $is_secret
 * @property string      $description
 * @property string      $details
 * @property int         $level
 * @property bool        $not_on_docker
 * @property int         $order
 * @property bool        $is_expert
 *
 * @method static ConfigsBuilder|Configs addSelect($column)
 * @method static ConfigsBuilder|Configs join(string $table, string $first, string $operator = null, string $second = null, string $type = 'inner', string $where = false)
 * @method static ConfigsBuilder|Configs joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false)
 * @method static ConfigsBuilder|Configs leftJoin(string $table, string $first, string $operator = null, string $second = null)
 * @method static ConfigsBuilder|Configs newModelQuery()
 * @method static ConfigsBuilder|Configs newQuery()
 * @method static ConfigsBuilder|Configs orderBy($column, $direction = 'asc')
 * @method static ConfigsBuilder|Configs query()
 * @method static ConfigsBuilder|Configs select($columns = [])
 * @method static ConfigsBuilder|Configs whereCat($value)
 * @method static ConfigsBuilder|Configs whereConfidentiality($value)
 * @method static ConfigsBuilder|Configs whereDescription($value)
 * @method static ConfigsBuilder|Configs whereId($value)
 * @method static ConfigsBuilder|Configs whereIn(string $column, string $values, string $boolean = 'and', string $not = false)
 * @method static ConfigsBuilder|Configs whereKey($value)
 * @method static ConfigsBuilder|Configs whereNotIn(string $column, string $values, string $boolean = 'and')
 * @method static ConfigsBuilder|Configs whereTypeRange($value)
 * @method static ConfigsBuilder|Configs whereValue($value)
 *
 * @mixin \Eloquent
 */
class Configs extends Model
{
	use ThrowsConsistentExceptions;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var list<string>
	 */
	protected $fillable = ['key', 'value', 'cat', 'type_range', 'is_secret', 'description', 'level', 'not_on_docker', 'order'];

	/**
	 *  this is a parameter for Laravel to indicate that there is no created_at, updated_at columns.
	 */
	public $timestamps = false;

	/**
	 * @param $query
	 *
	 * @return ConfigsBuilder
	 */
	public function newEloquentBuilder($query): ConfigsBuilder
	{
		return new ConfigsBuilder($query);
	}

	/**
	 * Sanity check.
	 *
	 * @param string|null $candidate_value
	 * @param string|null $message_template
	 *
	 * @return string
	 */
	public function sanity(?string $candidate_value, ?string $message_template = null): string
	{
		$message = '';
		$val_range = [
			ConfigType::BOOL->value => explode('|', ConfigType::BOOL->value),
			ConfigType::TERNARY->value => explode('|', ConfigType::TERNARY->value),
		];

		$message_template ??= 'Error: Wrong property for ' . $this->key . ', expected %s, got ' . ($candidate_value ?? 'NULL') . '.';
		switch ($this->type_range) {
			case ConfigType::STRING->value:
			case ConfigType::DISABLED->value:
				break;
			case ConfigType::STRING_REQ->value:
				if ($candidate_value === '' || $candidate_value === null) {
					$message = 'Error: ' . $this->key . ' empty or not set';
				}
				break;
			case ConfigType::INT->value:
				// we make sure that we only have digits in the chosen value.
				if (!ctype_digit(strval($candidate_value))) {
					$message = sprintf($message_template, 'positive integer or 0');
				}
				break;
			case ConfigType::POSTIIVE->value:
				if (!ctype_digit(strval($candidate_value)) || intval($candidate_value, 10) === 0) {
					$message = sprintf($message_template, 'strictly positive integer');
				}
				break;
			case ConfigType::BOOL->value:
			case ConfigType::TERNARY->value:
				if (!in_array($candidate_value, $val_range[$this->type_range], true)) { // BOOL or TERNARY
					$message = sprintf($message_template, implode(' or ', $val_range[$this->type_range]));
				}
				break;
			case ConfigType::ADMIN_USER->value:
				$admin_candidate = User::where('may_administrate', true)
					->where('id', '=', $candidate_value)
					->first();
				if ($admin_candidate === null) {
					$message = sprintf($message_template, 'a valid admin user ID');
				}
				break;
			case ConfigType::LICENSE->value:
				if (LicenseType::tryFrom($candidate_value) === null) {
					$message = sprintf($message_template, 'a valid license');
				}
				break;
			case ConfigType::CURRENCY->value:
				try {
					$bundle = \ResourceBundle::create('en', 'ICUDATA-curr');
					$currencies = $bundle->get('Currencies');
					$found = false;
					foreach ($currencies as $code => $data) {
						$found = ($code === $candidate_value);
						if ($found) {
							break; // we found it, stop searching
						}
					}
					if (!$found) {
						$message = sprintf($message_template, 'a valid ISO 4217 currency code');
						break;
					}
					break;
				} catch (\Throwable) {
					// @codeCoverageIgnoreStart
					$message = 'php-intl extension is missing. Cannot validate currency code.';
					break;
					// @codeCoverageIgnoreEnd
				}
			case ConfigType::MAP_PROVIDER->value:
				if (MapProviders::tryFrom($candidate_value) === null) {
					$message = sprintf($message_template, 'a valid map provider');
				}
				break;
			default:
				$values = explode('|', $this->type_range);
				if (!in_array($candidate_value, $values, true)) {
					$message = sprintf($message_template, implode(' or ', $values));
				}
				break;
		}

		return $message;
	}

	/**
	 * Update Lychee configuration
	 * Note that we must invalidate the cache now.
	 *
	 * @param string          $key
	 * @param string|int|bool $value
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 * @throws QueryBuilderException
	 */
	public static function set(string $key, string|int|bool|\BackedEnum $value): void
	{
		try {
			/** @var Configs $config */
			$config = Configs::query()
				->where('key', '=', $key)
				->firstOrFail();

			// For BackEnm we take the value. In theory this is no longer necessary because we enforce at the column type.
			if ($value instanceof \BackedEnum) {
				$value = $value->value;
			}

			$str_value = match (gettype($value)) {
				'boolean' => $value === true ? '1' : '0',
				'integer', 'string' => strval($value),
				// @codeCoverageIgnoreStart
				default => throw new LycheeAssertionError('Unexpected type'),
				// @codeCoverageIgnoreEnd
			};

			/**
			 * Sanity check. :).
			 */
			$message = $config->sanity($str_value);
			if ($message !== '') {
				throw new InvalidConfigOption($message);
			}
			$config->value = $str_value;
			$config->save();
			// @codeCoverageIgnoreStart
		} catch (ModelNotFoundException $e) {
			throw new InvalidConfigOption('key ' . $key . ' not found!', $e);
		} catch (ModelDBException $e) {
			throw new InvalidConfigOption('Could not save configuration', $e);
			// @codeCoverageIgnoreEnd
		} finally {
			// invalidate cache.
			resolve(ConfigManager::class)->invalidateCache();
		}
	}
}
