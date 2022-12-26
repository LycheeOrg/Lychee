<?php

namespace App\Http\Resources;

use Illuminate\Container\Container;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource as BaseJsonResource;

class JsonResource extends BaseJsonResource
{
	/**
	 * We define a default constructor so it is no longer needed in other cases.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct(null);
	}

	/**
	 * Resolve the resource to an array.
	 *
	 * @param \Illuminate\Http\Request|null $request
	 *
	 * @return array
	 */
	public function resolve($request = null)
	{
		$request = $request ?? Container::getInstance()->make('request');

		$data = $this->toArrayRecursively($request);

		return $this->filter($data);
	}

	/**
	 * Recursively apply toArray in order to care care of futher BaseJsonResource.
	 *
	 * @param \Illuminate\Http\Request|null $request
	 *
	 * @return array
	 */
	private function toArrayRecursively($request = null): array
	{
		/** @var array|Arrayable|\JsonSerializable $data */
		$data = $this->toArray($request);
		if ($data instanceof Arrayable) {
			$data = $data->toArray();
		} elseif ($data instanceof \JsonSerializable) {
			$data = $data->jsonSerialize();
		}

		// Now that we have transformed the first layer as an array we need to check the subsequent layers
		foreach ($data as $k => $v) {
			if (is_object($v)) {
				if ($v instanceof JsonResource) {
					$data[$k] = $v->toArrayRecursively($request); // Here is the recursivity
				} elseif ($v instanceof BaseJsonResource) {
					$data[$k] = $v->toArray($request);
				} elseif ($v instanceof Arrayable) {
					$data[$k] = $v->toArray();
				} elseif ($v instanceof \BackedEnum) {
					$data[$k] = $v->value;
				}
			}
		}

		return $data;
	}
}
