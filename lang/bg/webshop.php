<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Webshop page
    |--------------------------------------------------------------------------
    */
    "card" => [
        "cardHolder" => "Притежател на карта",
        "fullName" => "Пълно име",
        "expires" => "Изтича",
        "MM" => "ММ",
        "YY" => "ГГ"
    ],
    "cardForm" => [
        "cardNumber" => "Номер на карта",
        "cardName" => "Име на карта",
        "expirationDate" => "Срок на валидност",
        "month" => "Месец",
        "year" => "Година",
        "CVV" => "CVV",
        "submit" => "Изпрати",
        "invalidCardNumber" => "Невалиден номер на карта"
    ],
    "basketList" => [
        "basket" => "Количка",
        "size" => "Размер",
        "license" => "Лиценз",
        "notes" => "Бележки",
        "removeItem" => "Премахни артикул",
        "clearBasket" => "Изчисти количката",
        "total" => "Общо:",
        "proceedToCheckout" => "Премини към плащане",
        "emptyBasket" => "Вашата количка е празна."
    ],
    "checkout" => [
        "checkout" => "Плащане",
        "yourInfo" => "Вашата информация",
        "payment" => "Плащане",
        "confirmation" => "Потвърждение",
        "next" => "Напред",
        "back" => "Назад",
        "thankYou" => "Благодарим за покупката!",
        "orderNumber" => "Номер на вашата поръчка:",
        "transactionId" => "Идентификатор на транзакцията:",
        "noteWarning" => "Моля",
        "noteTransactionId" => "запишете вашия идентификатор на транзакция и",
        "noteOrderNumber" => "номер на поръчката",
        "noteReason" => "тъй като ще са ви нужни за достъп до съдържанието.",
        "enjoyPurchase" => "Приятна покупка!",
        "toMyDownloads" => "Към моите изтегляния",
        "toTheGallery" => "Към галерията",
        "offlineThankYou" => "Благодарим за покупката!",
        "offlinePaymentMessage" => "Ще се свържем с вас по имейл с инструкции за плащане.",
        "offlineProcessingMessage" => "Ще ви уведомим, когато снимките са готови за изтегляне."
    ],
    "orderDownload" => [
        "order" => "Поръчка %d",
        "orderAccessRequired" => "Необходим достъп до поръчката",
        "provideTransactionId" => "Моля, предоставете идентификатора на транзакцията, за да видите детайлите на поръчката.",
        "enterTransactionId" => "Въведете идентификатор на транзакция",
        "loadOrder" => "Зареди поръчката",
        "orderDetails" => "Детайли на поръчката",
        "transactionId" => "Идентификатор на транзакция:",
        "orderSummary" => "Обобщение на поръчката",
        "for" => "За:",
        "status" => "Статус:",
        "total" => "Общо:",
        "paid" => "Платено:",
        "notPaid" => "не е платено",
        "lastUpdate" => "Последна актуализация:",
        "items" => "Артикули",
        "deliver" => "Достави",
        "edit" => "Редактиране",
        "view" => "Преглед",
        "enterContentUrl" => "Въведете URL на съдържанието тук.",
        "download" => "Изтегли",
        "downloadNotAvailable" => "Изтеглянето не е налично (още)",
        "copiedToClipboard" => "Копирано в клипборда",
        "orderLinkCopied" => "Линк към поръчката копиран в клипборда",
        "couldNotCopy" => "Не можа да се копира в клипборда.",
        "somethingWentWrong" => "Нещо се обърка",
        "couldNotMarkDelivered" => "Не можа да се отбележи артикул като доставен."
    ],
    "status" => [
        "pending" => "В очакване",
        "paid" => "Платено",
        "offline" => "Офлайн",
        "completed" => "Завършено",
        "processing" => "Обработва се",
        "failed" => "Неуспешно",
        "closed" => "Затворено"
    ],
    "orderList" => [
        "orders" => "Поръчки",
        "numStaleOrders" => "Брой стари поръчки: %d",
        "cleanStaleOrders" => "Почисти стари поръчки",
        "client" => "Клиент",
        "transactionId" => "Идентификатор на транзакция",
        "status" => "Статус",
        "amount" => "Сума"
    ],
    "purchasablesList" => [
        "purchasables" => "Продукти за покупка",
        "title" => "Заглавие",
        "description" => "Описание",
        "notes" => "Бележки",
        "prices" => "Цени"
    ],
    "disclaimer" => [
        "title" => "Отказ от отговорност",
        "message" => "Lychee е разработен под <a href='https://lycheeorg.dev/license' class='text-primary-400'>MIT лиценз</a>. Това означава, че <span class='text-muted-color-emphasis'>LycheeOrg не носи отговорност</span> за <span class='text-muted-color-emphasis'>някакви проблеми или загуби</span>, възникнали при използването на модула за електронен магазин и/или възможностите за обработка на плащания. Важно е да проверите и <span class='text-muted-color-emphasis'>да се уверите, че вашата конфигурация работи правилно и сигурно преди използване в продукционна среда.</span>",
        "iUnderstand" => "Разбирам"
    ],
    "infoSection" => [
        "yourInfo" => "Вашата информация",
        "mustBeLoggedIn" => "Трябва да сте влезли, за да продължите с плащането.",
        "goToLogin" => "Вход",
        "notLoggedInMessage" => "Не сте влезли! Моля, въведете вашия имейл, за да продължите.",
        "loggedInWithEmail" => "Влезли сте като <span class='text-primary'>%s</span> (%s). Можете да промените имейл адреса, ако искате да получавате известия за поръчки на друг адрес.",
        "loggedInWithoutEmail" => "Влезли сте като <span class='text-primary'>%s</span>. Можете да зададете имейл адрес, ако искате да получавате известия за поръчки.",
        "emailUsageNote" => "Вашият имейл ще се използва само за известия за поръчки.",
        "consentAgreement" => "Съгласявам се с <a href='%s' target='_blank' class='text-primary-600 hover:underline'>политиката за поверителност</a> и <a href='%s' target='_blank' class='text-primary-600 hover:underline'>условията за ползване</a>."
    ],
    "errors" => [
        "emailRequired" => "Имейлът е задължителен за гост плащане.",
        "invalidEmail" => "Моля, въведете валиден имейл адрес.",
        "noData" => "НЯМА ДАННИ"
    ],
    "orderLegend" => [
        "needHelp" => "Нуждаете се от помощ?",
        "legend" => "Легенда:",
        "pendingDesc" => "Поръчката е създадена, но все още не е платена.",
        "processingDesc" => "Плащането се обработва.",
        "offlineDesc" => "Поръчката е маркирана за ръчно плащане.",
        "completedDesc" => "Поръчката е платена.",
        "closedDesc" => "Поръчката е доставена.",
        "cancelledDesc" => "Плащането е отменено.",
        "failedDesc" => "Плащането е неуспешно.",
        "flowsIntro" => "Има няколко възможни потока за управление на поръчките, описани по-долу:",
        "offlineExplanation" => "Поръчка със статус %s означава, че плащането ще се обработва ръчно, например чрез банков превод или наложен платеж. Администраторът на магазина е отговорен за актуализиране на статуса на поръчката на %s, след като плащането бъде потвърдено чрез бутона „Маркирай като платено“ в детайлите на поръчката.",
        "offlineStatus" => "офлайн",
        "completedStatus" => "завършено",
        "closedStatus" => "затворено",
        "offlineExplanationPart1" => "Поръчка със статус",
        "offlineExplanationPart2" => "означава, че плащането ще се обработва ръчно, например чрез банков превод или наложен платеж. Администраторът на магазина е отговорен за актуализиране на статуса на поръчката на",
        "offlineExplanationPart3" => "след като плащането бъде потвърдено чрез бутона „Маркирай като платено“ в детайлите на поръчката.",
        "closedExplanationPart1" => "След като поръчката достигне статуса",
        "closedExplanationPart2" => ", тя се счита за завършена и повече действия не могат да бъдат предприети."
    ],
    "orderListAction" => [
        "markAsPaid" => "Маркирай като платено",
        "requireAttention" => "Изисква внимание",
        "markAsDelivered" => "Маркирай като доставено",
        "viewDetails" => "Преглед на детайли"
    ],
    "orderSummary" => [
        "title" => "Обобщение на вашата поръчка",
        "size" => "Размер:",
        "license" => "Лиценз:",
        "notes" => "Бележки:",
        "total" => "Общо:"
    ],
    "paymentForm" => [
        "selectProvider" => "Изберете вашия доставчик на плащане",
        "selectProviderPlaceholder" => "Изберете доставчик на плащане",
        "pciCompliant" => "Това плащане е съвместимо с %s.<br />Данните на вашата карта се обработват сигурно от %s.",
        "enterInfo" => "Въведете вашата информация за %s"
    ],
    "paymentInProgress" => [
        "message" => "Плащане в процес...",
        "cancel" => "отмяна"
    ],
    "useOrder" => [
        "copiedToClipboard" => "Копирано в клипборда",
        "transactionIdCopied" => "Идентификатор на транзакция копиран в клипборда"
    ],
    "albumPurchasable" => [
        "notPurchasableYet" => "Този албум все още не е достъпен за покупка.",
        "descriptionPlaceholder" => "Описание за клиентите",
        "ownerNotePlaceholder" => "Бележка на собственика",
        "setPurchasable" => "Маркирай като достъпен за покупка",
        "setPurchasablePropagate" => "Маркирай като достъпен за покупка и разпространи",
        "disable" => "Деактивирай",
        "update" => "Обнови",
        "setAtLeastOnePrice" => "Задайте поне една цена.",
        "success" => "Успех",
        "error" => "Грешка",
        "albumNowPurchasable" => "Албумът вече е достъпен за покупка",
        "albumNoLongerPurchasable" => "Албумът вече не е достъпен за покупка"
    ],
    "pricesInput" => [
        "licenseType" => "Тип лиценз",
        "variant" => "Вариант",
        "duplicateError" => "Има дублирани цени (същият тип лиценз и вариант на размер).",
        "addPrice" => "Добави цена"
    ],
    "useStepTwo" => [
        "fakeCardClipboard" => "Фалшивият номер на карта е наличен в клипборда",
        "paymentSuccess" => "Плащането е успешно обработено.",
        "redirectError" => "Искаше се пренасочване, но целта липсва.",
        "finalizationError" => "Искаше се финализиране, но целта липсва.",
        "orderFinalizedSuccess" => "Поръчката е успешно финализирана.",
        "orderFinalizationFailed" => "Финализирането на поръчката не бе успешно.",
        "badRequest" => "Грешна заявка",
        "invalidInput" => "Заявката е невалидна. Моля, проверете данните си.",
        "success" => "Успех",
        "error" => "Грешка"
    ],
    "useMollie" => [
        "error" => "Грешка",
        "profileNotConfigured" => "ID на профила в Mollie не е конфигуриран.",
        "somethingWentWrong" => "Нещо се обърка с Mollie."
    ],
    "usePaypal" => [
        "error" => "Грешка",
        "client_id_missing" => "PayPal client ID не е конфигуриран.",
        "sdkLoadError" => "Грешка при зареждане на PayPal JS SDK",
        "sdkLoadErrorDetail" => "Неуспешно зареждане на PayPal JS SDK скрипт",
        "paymentError" => "Грешка при PayPal плащане",
        "paymentErrorDetail" => "Възникна грешка по време на процеса на плащане чрез PayPal."
    ],
    "buyMeActions" => [
        "success" => "Успех",
        "addedToOrder" => "Добавено към поръчката",
        "photoAddedToOrder" => "%s добавено към вашата поръчка за %s"
    ],
    "cancelledFailed" => [
        "paymentCancelled" => "Плащането е отменено",
        "paymentCancelledMessage" => "Плащането е отменено.",
        "paymentFailed" => "Плащането е неуспешно",
        "paymentFailedMessage" => "Не успяхме да потвърдим вашето плащане. Моля, опитайте отново или се свържете с поддръжката, ако проблемът продължава."
    ]
];
