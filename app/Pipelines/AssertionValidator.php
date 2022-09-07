<?php

namespace App\Pipelines;

use App\Pipelines\Pipes\CheckCredentialIsForUser;
use Illuminate\Pipeline\Pipeline;
use Laragear\WebAuthn\Assertion\Validator\Pipes\CheckChallengeSame;
use Laragear\WebAuthn\Assertion\Validator\Pipes\CheckCredentialIsWebAuthnGet;
use Laragear\WebAuthn\Assertion\Validator\Pipes\CheckOriginSecure;
use Laragear\WebAuthn\Assertion\Validator\Pipes\CheckPublicKeyCounterCorrect;
use Laragear\WebAuthn\Assertion\Validator\Pipes\CheckPublicKeySignature;
use Laragear\WebAuthn\Assertion\Validator\Pipes\CheckRelyingPartyHashSame;
use Laragear\WebAuthn\Assertion\Validator\Pipes\CheckRelyingPartyIdContained;
use Laragear\WebAuthn\Assertion\Validator\Pipes\CheckTypeIsPublicKey;
use Laragear\WebAuthn\Assertion\Validator\Pipes\CheckUserInteraction;
use Laragear\WebAuthn\Assertion\Validator\Pipes\CompileAuthenticatorData;
use Laragear\WebAuthn\Assertion\Validator\Pipes\CompileClientDataJson;
use Laragear\WebAuthn\Assertion\Validator\Pipes\IncrementCredentialCounter;
use Laragear\WebAuthn\Assertion\Validator\Pipes\RetrieveChallenge;
use Laragear\WebAuthn\Assertion\Validator\Pipes\RetrievesCredentialId;

/**
 * This validator is literally a copy of the one in WebAuthn,
 * It is needed because Laragear makes use of is() which is wrong in Laravel
 * We Update the CheckCredentialsForUser pipe for this reason.
 *
 * TODO: remove once Laravel fixed their stupidity: https://github.com/laravel/framework/pull/43860
 *
 * @method \Laragear\WebAuthn\Assertion\Validator\AssertionValidation thenReturn()
 */
class AssertionValidator extends Pipeline
{
	/**
	 * The array of class pipes.
	 *
	 * @var array
	 */
	protected $pipes = [
		RetrieveChallenge::class,
		RetrievesCredentialId::class,
		CheckCredentialIsForUser::class,
		CheckTypeIsPublicKey::class,
		CompileAuthenticatorData::class,
		CompileClientDataJson::class,
		CheckCredentialIsWebAuthnGet::class,
		CheckChallengeSame::class,
		CheckOriginSecure::class,
		CheckRelyingPartyIdContained::class,
		CheckRelyingPartyHashSame::class,
		CheckUserInteraction::class,
		CheckPublicKeySignature::class,
		CheckPublicKeyCounterCorrect::class,
		IncrementCredentialCounter::class,
	];
}
