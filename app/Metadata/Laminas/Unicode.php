<?php

// Copyright (c) 2020 Laminas Project a Series of LF Projects, LLC.
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are met:
//
// - Redistributions of source code must retain the above copyright notice, this
//   list of conditions and the following disclaimer.
//
// - Redistributions in binary form must reproduce the above copyright notice,
//   this list of conditions and the following disclaimer in the documentation
//   and/or other materials provided with the distribution.
//
// - Neither the name of Laminas Foundation nor the names of its contributors may
//   be used to endorse or promote products derived from this software without
//   specific prior written permission.
//
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
// ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
// DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
// ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
// (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
// LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
// ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
// (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
// SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

namespace App\Metadata\Laminas;

use App\Contracts\Laminas\DecoratorInterface as Decorator;
use App\Exceptions\Internal\LycheeLogicException;

/**
 * Unicode Decorator for Laminas\Text\Table.
 */
final class Unicode implements Decorator
{
	/**
	 * {@inheritDoc}
	 */
	public function getTopLeft(): string
	{
		return $this->_uniChar(0x250C);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTopRight(): string
	{
		return $this->_uniChar(0x2510);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBottomLeft(): string
	{
		return $this->_uniChar(0x2514);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBottomRight(): string
	{
		return $this->_uniChar(0x2518);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getVertical(): string
	{
		return $this->_uniChar(0x2502);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHorizontal(): string
	{
		return $this->_uniChar(0x2500);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getCross(): string
	{
		return $this->_uniChar(0x253C);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getVerticalRight(): string
	{
		return $this->_uniChar(0x251C);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getVerticalLeft(): string
	{
		return $this->_uniChar(0x2524);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHorizontalDown(): string
	{
		return $this->_uniChar(0x252C);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHorizontalUp(): string
	{
		return $this->_uniChar(0x2534);
	}

	/**
	 * Convert am unicode character code to a character.
	 *
	 * @param int $code
	 *
	 * @codeCoverageIgnore This is from Laminas. We trust it works.
	 */
	// @codingStandardsIgnoreStart
	private function _uniChar(int $code): string
	{
		// @codingStandardsIgnoreEnd
		if ($code <= 0x7F) {
			return \chr($code);
		}
		if ($code <= 0x7FF) {
			return \chr(0xC0 | $code >> 6)
				  . \chr(0x80 | $code & 0x3F);
		}
		if ($code <= 0xFFFF) {
			return \chr(0xE0 | $code >> 12)
				  . \chr(0x80 | $code >> 6 & 0x3F)
				  . \chr(0x80 | $code & 0x3F);
		}
		if ($code <= 0x10FFFF) {
			return \chr(0xF0 | $code >> 18)
				  . \chr(0x80 | $code >> 12 & 0x3F)
				  . \chr(0x80 | $code >> 6 & 0x3F)
				  . \chr(0x80 | $code & 0x3F);
		}

		throw new LycheeLogicException('Code point requested outside of Unicode range');
	}
}
