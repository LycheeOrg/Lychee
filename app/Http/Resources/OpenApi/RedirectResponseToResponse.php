<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\OpenApi;

use Dedoc\Scramble\Extensions\TypeToSchemaExtension;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\Type;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectResponseToResponse extends TypeToSchemaExtension
{
	/**
	 * Laravel's RedirectResponse implements Responsable and its toResponse() returns itself.
	 * Scramble's built-in ResponsableTypeToSchema resolves toResponse() and recurses on the result,
	 * which never ends. We document redirects here before that extension is reached.
	 */
	public function shouldHandle(Type $type): bool
	{
		return $type instanceof ObjectType && $type->isInstanceOf(RedirectResponse::class);
	}

	/**
	 * @param Type $type the type being transformed to response
	 */
	public function toResponse(Type $type): Response
	{
		return Response::make(302)->setDescription('Redirect');
	}
}
