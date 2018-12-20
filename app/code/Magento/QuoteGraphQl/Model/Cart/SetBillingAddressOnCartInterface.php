<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Extension point for setting billing address for a specified shopping cart
 *
 * All objects that are responsible for setting billing address on a cart via GraphQl
 * should implement this interface.
 */
interface SetBillingAddressOnCartInterface
{
    /**
     * Set billing address for a specified shopping cart
     *
     * @param ContextInterface $context
     * @param CartInterface $cart
     * @param array $billingAddress
     * @return void
     * @throws GraphQlInputException
     */
    public function execute(ContextInterface $context, CartInterface $cart, array $billingAddress): void;
}
