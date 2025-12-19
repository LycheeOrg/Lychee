<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Webshop page
    |--------------------------------------------------------------------------
    */
    "card" => [
        "cardHolder" => "Card Holder",
        "fullName" => "Full Name",
        "expires" => "Expires",
        "MM" => "MM",
        "YY" => "YY"

    ],
    "cardForm" => [
        "cardNumber" => "Card Number",
        "cardName" => "Card Name",
        "expirationDate" => "Expiration Date",
        "month" => "Month",
        "year" => "Year",
        "CVV" => "CVV",
        "submit" => "Submit",
        "invalidCardNumber" => "Invalid Card Number"
    ],
    "basketList" => [
        "basket" => "Basket",
        "size" => "Size",
        "license" => "License",
        "notes" => "Notes",
        "removeItem" => "Remove item",
        "clearBasket" => "Clear basket",
        "total" => "Total:",
        "proceedToCheckout" => "Proceed to Checkout",
        "emptyBasket" => "Your basket is empty."
    ],
    "checkout" => [
        "checkout" => "Checkout",
        "yourInfo" => "Your info",
        "payment" => "Payment",
        "confirmation" => "Confirmation",
        "next" => "Next",
        "back" => "Back",
        "thankYou" => "Thank you for your purchase!",
        "orderNumber" => "Your order number is:",
        "transactionId" => "Your transaction id is:",
        "noteWarning" => "Please",
        "noteTransactionId" => "note your transaction id and",
        "noteOrderNumber" => "your order number",
        "noteReason" => "as you will need them to access your content.",
        "enjoyPurchase" => "Enjoy your purchase!",
        "toMyDownloads" => "To my downloads",
        "toTheGallery" => "To the gallery",
        "offlineThankYou" => "Thank you for your purchase!",
        "offlinePaymentMessage" => "We will get in touch with you shortly via email with the payment instructions.",
        "offlineProcessingMessage" => "We will notify you once your photos are ready to be downloaded."
    ],
    "orderDownload" => [
        "order" => "Order %d",
        "orderAccessRequired" => "Order Access Required",
        "provideTransactionId" => "Please provide the transaction ID to access your order details.",
        "enterTransactionId" => "Enter transaction ID",
        "loadOrder" => "Load Order",
        "orderDetails" => "Order Details",
        "transactionId" => "Transaction ID:",
        "orderSummary" => "Order Summary",
        "for" => "For:",
        "status" => "Status:",
        "total" => "Total:",
        "paid" => "Paid:",
        "notPaid" => "not paid",
        "lastUpdate" => "Last update:",
        "items" => "Items",
        "deliver" => "Deliver",
        "edit" => "Edit",
        "view" => "View",
        "enterContentUrl" => "Enter content URL here.",
        "download" => "Download",
        "downloadNotAvailable" => "Download not available (yet)",
        "copiedToClipboard" => "Copied to clipboard",
        "orderLinkCopied" => "Order link copied to clipboard"
    ],
    "status" => [
        "pending" => "Pending",
        "paid" => "Paid",
        "offline" => "Offline",
        "completed" => "Completed",
        "processing" => "Processing",
        "failed" => "Failed",
        "closed" => "Closed"
    ],
    "orderList" => [
        "orders" => "Orders",
        "numStaleOrders" => "Number of stale orders: %d",
        "cleanStaleOrders" => "Clean stale orders",
        "client" => "Client",
        "transactionId" => "Transaction ID",
        "status" => "Status",
        "amount" => "Amount"
    ],
    "purchasablesList" => [
        "purchasables" => "Purchasables",
        "title" => "Title",
        "description" => "Description",
        "notes" => "Notes",
        "prices" => "Prices"
    ],
    "disclaimer" => [
        "title" => "Disclaimer",
        "message" => "Lychee is developed under the <a href='https://lycheeorg.dev/license' class='text-primary-400'>MIT license</a>. This means that <span class='text-muted-color-emphasis'>LycheeOrg is not responsible</span> nor liable <span class='text-muted-color-emphasis'>for any issues or losses</span> arising from the use of the webshop module and/or the payment processing capabilities. It is critical that you verify and <span class='text-muted-color-emphasis'>ensure that your setup is working correctly and securely before using it in a production environment.</span>",
        "iUnderstand" => "I understand"
    ],
    "infoSection" => [
        "yourInfo" => "Your info",
        "mustBeLoggedIn" => "You must be logged in to proceed with the checkout.",
        "goToLogin" => "Go to login",
        "notLoggedInMessage" => "You are not logged in! Please provide your email address to continue.",
        "loggedInWithEmail" => "You are logged in as <span class='text-primary'>%s</span> (%s). You can change your email address if you want to receive order-related communication at a different address.",
        "loggedInWithoutEmail" => "You are logged in as <span class='text-primary'>%s</span>. You set an email address if you want to receive order-related communication.",
        "emailUsageNote" => "Your email will only be used for order-related communication.",
        "consentAgreement" => "I agree to the <a href='%s' target='_blank' class='text-primary-600 hover:underline'>privacy policy</a> and <a href='%s' target='_blank' class='text-primary-600 hover:underline'>terms of service</a>."
    ],
    "errors" => [
        "emailRequired" => "Email is required for guest checkout.",
        "invalidEmail" => "Please enter a valid email address.",
        "noData" => "NO DATA"
    ],
    "orderLegend" => [
        "needHelp" => "Need help?",
        "legend" => "Legend:",
        "pendingDesc" => "Order is created but not paid yet.",
        "processingDesc" => "Payment is being processed.",
        "offlineDesc" => "Order is marked as to be paid manually.",
        "completedDesc" => "Order has been paid.",
        "closedDesc" => "Order has been delivered.",
        "cancelledDesc" => "Payment has been cancelled.",
        "failedDesc" => "Payment has failed.",
        "flowsIntro" => "There are multiple possible order control flows as described bellow:",
        "offlineExplanation" => "An order in the %s status indicates that the payment will be handled manually, such as through bank transfer or cash on delivery. The admin of the webshop is responsible for updating the order status to %s once the payment is confirmed by clicking the \"Mark as Paid\" button in the order details.",
        "offlineStatus" => "offline",
        "completedStatus" => "completed",
        "closedStatus" => "closed",
        "offlineExplanationPart1" => "An order in the",
        "offlineExplanationPart2" => "status indicates that the payment will be handled manually, such as through bank transfer or cash on delivery. The admin of the webshop is responsible for updating the order status to",
        "offlineExplanationPart3" => "once the payment is confirmed by clicking the \"Mark as Paid\" button in the order details.",
        "closedExplanationPart1" => "Once an order reaches the",
        "closedExplanationPart2" => "status, it is considered finalized and no further actions can be taken."
    ],
    "orderListAction" => [
        "markAsPaid" => "Mark as Paid",
        "requireAttention" => "Require Attention",
        "markAsDelivered" => "Mark as Delivered",
        "viewDetails" => "View Details"
    ],
    "orderSummary" => [
        "title" => "Summary of your order",
        "size" => "Size:",
        "license" => "License:",
        "notes" => "Notes:",
        "total" => "Total:"
    ],
    "paymentForm" => [
        "selectProvider" => "Select your payment provider",
        "selectProviderPlaceholder" => "Select a payment provider",
        "pciCompliant" => "This payment is %s compliant.<br />Your card details are processed securely by %s.",
        "enterInfo" => "Enter your info for %s"
    ],
    "paymentInProgress" => [
        "message" => "Payment in progess...",
        "cancel" => "cancel"
    ],
    "useOrder" => [
        "copiedToClipboard" => "Copied to clipboard",
        "transactionIdCopied" => "Transaction ID copied to clipboard"
    ],
    "albumPurchasable" => [
        "notPurchasableYet" => "This album is not purchasable (yet).",
        "descriptionPlaceholder" => "Description for clients",
        "ownerNotePlaceholder" => "Owner's Note",
        "setPurchasable" => "Set Purchasable",
        "setPurchasablePropagate" => "Set Purchasable and propagate",
        "disable" => "Disable",
        "update" => "Update",
        "setAtLeastOnePrice" => "Set at least one price.",
        "success" => "Success",
        "error" => "Error",
        "albumNowPurchasable" => "Album is now purchasable",
        "albumNoLongerPurchasable" => "Album is no longer purchasable"
    ],
    "pricesInput" => [
        "licenseType" => "License Type",
        "variant" => "Variant",
        "duplicateError" => "There are duplicate prices (same license type and size variant).",
        "addPrice" => "Add Price"
    ],
    "useStepTwo" => [
        "fakeCardClipboard" => "Fake card number available in clipboard",
        "paymentSuccess" => "Payment processed successfully.",
        "redirectError" => "Redirection requested but target is absent.",
        "finalizationError" => "Finalization requested but target is absent.",
        "orderFinalizedSuccess" => "Order finalized successfully.",
        "orderFinalizationFailed" => "Order finalization failed.",
        "badRequest" => "Bad Request",
        "invalidInput" => "The request was invalid. Please check your input.",
        "success" => "Success",
        "error" => "Error"
    ],
    "useMollie" => [
        "error" => "Error",
        "profileNotConfigured" => "Mollie profile ID is not configured."
    ],
    "usePaypal" => [
        "error" => "Error",
        "client_id_missing" => "PayPal client ID is not configured.",
        "sdkLoadError" => "PayPal JS SDK Load Error",
        "sdkLoadErrorDetail" => "Failed to load the PayPal JS SDK script",
        "paymentError" => "PayPal Payment Error",
        "paymentErrorDetail" => "An error occurred during the PayPal payment process."
    ],
    "buyMeActions" => [
        "success" => "Success",
        "addedToOrder" => "Added to order",
        "photoAddedToOrder" => "%s added to your order for %s"
    ],
    "cancelledFailed" => [
        "paymentCancelled" => "Payment cancelled",
        "paymentCancelledMessage" => "Payment has been cancelled.",
        "paymentFailed" => "Payment failed",
        "paymentFailedMessage" => "We were not able to confirm your payment. Please try again or contact support if the problem persists."
    ]
];
