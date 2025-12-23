<?php

namespace App\Http;

use Illuminate\Http\Request as BaseRequest;
use LycheeVerify\Contract\VerifyInterface;
use LycheeVerify\VerifyTrait;

class Request extends BaseRequest implements VerifyInterface
{
	use VerifyTrait;
	use AttributesTraits;
}
