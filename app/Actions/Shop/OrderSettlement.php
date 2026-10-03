<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Shop;

use App\Enum\PaymentStatusType;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Marks an order as paid, at most once.
 *
 * Shared by every settlement path — the browser return, the gateway
 * completion response and the signed payment notification — so that all of
 * them give the same guarantee regardless of provider.
 */
class OrderSettlement
{
	/**
	 * Settle an order, at most once.
	 *
	 * The browser return and an inbound payment notification can arrive at the
	 * same moment, and both would otherwise observe PROCESSING and settle the
	 * order — dispatching OrderCompleted (and thus fulfilling) twice. The row
	 * is locked and re-read inside a transaction, so exactly one caller
	 * performs the transition; the loser sees the fresh state and reports that
	 * it changed nothing.
	 *
	 * @param Order  $order          The order to settle
	 * @param string $transaction_id The reference to store on the order
	 *
	 * @return bool Whether THIS call completed the order
	 */
	/**
	 * Mark an order as failed, only if it is still PROCESSING.
	 *
	 * Mirror of {@see settle()} for the failure transition: a failed or
	 * expired signal racing a successful settlement must never downgrade an
	 * order that another caller just completed. The row is locked and
	 * re-read, and only a PROCESSING order takes the transition; any other
	 * state is adopted as-is.
	 *
	 * @param Order $order The order to mark as failed
	 *
	 * @return bool Whether THIS call failed the order
	 */
	public function fail(Order $order): bool
	{
		return DB::transaction(function () use ($order): bool {
			$fresh = Order::query()->whereKey($order->getKey())->lockForUpdate()->first();

			if ($fresh === null || $fresh->status !== PaymentStatusType::PROCESSING) {
				if ($fresh !== null) {
					// Someone else moved it first (e.g. the settlement won the
					// race); adopt their state without claiming the transition.
					$order->refresh();
				}

				return false;
			}

			// Saved through the caller's instance, while this transaction
			// holds the row lock — same convention as settle().
			$order->status = PaymentStatusType::FAILED;
			$order->save();

			return true;
		});
	}

	public function settle(Order $order, string $transaction_id): bool
	{
		return DB::transaction(function () use ($order, $transaction_id): bool {
			$fresh = Order::query()->whereKey($order->getKey())->lockForUpdate()->first();

			if ($fresh === null) {
				return false;
			}

			if (in_array($fresh->status, [PaymentStatusType::COMPLETED, PaymentStatusType::CLOSED], true)) {
				// Someone else settled it first; adopt their state without
				// claiming the transition.
				$order->refresh();

				return false;
			}

			// Saved through the caller's instance, while this transaction holds
			// the row lock, so wasChanged('status') is true for the winner only.
			$order->markAsPaid($transaction_id);

			return true;
		});
	}
}
