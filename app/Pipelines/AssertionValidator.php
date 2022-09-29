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
 * This validator is literally a copy of {@link Laragear\WebAuthn\Assertion\Validator\Pipes\AssertionValidator},
 * This copy is needed because {@link Laragear\WebAuthn\Assertion\Validator\Pipes\CheckCredentialIsForUser}
 * uses isNot() from {@link Illuminate\Database\Eloquent\Relations\Concerns\ComparesRelatedModels} which
 * internally calls is() and subsequently compareKeys().
 *
 * compareKeys() uses the lose check empty() on id to prune the null ones.
 * However, this also returns false on 0. As a result, two related models with a same id of 0 (admin in our case)
 * will be considered different entities.
 *
 * For this reason, we replace {@link Laragear\WebAuthn\Assertion\Validator\Pipes\CheckCredentialIsForUser}
 * by our own {@link App\Pipelines\Pipes\CheckCredentialIsForUser} to achieve the expected behaviour.
 *
 * The Laravel team, in their brilliant stupidity, decided this was not worth their reading consideration.
 * See here: https://github.com/laravel/framework/pull/43860
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
