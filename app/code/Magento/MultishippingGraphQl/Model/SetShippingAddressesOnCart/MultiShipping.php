<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MultishippingGraphQl\Model\Resolver\SetShippingAddressesOnCart;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Multishipping\Model\Checkout\Type\Multishipping as MultishippingModel;
use Magento\MultishippingGraphQl\Model\Resolver\SetShippingAddressesOnCart\MultiShipping\ShippingItemsMapper;
use Magento\Quote\Api\Data\CartInterface;
use Magento\QuoteGraphQl\Model\Cart\SetShippingAddressesOnCartInterface;

/**
 * Multishipping address assigning flow
 */
class MultiShipping implements SetShippingAddressesOnCartInterface
{
    /**
     * @var MultishippingModel
     */
    private $multishippingModel;

    /**
     * @var ShippingItemsMapper
     */
    private $shippingItemsInformationMapper;

    /**
     * @param MultishippingModel $multishippingModel
     * @param ShippingItemsMapper $shippingItemsInformationMapper
     */
    public function __construct(
        MultishippingModel $multishippingModel,
        ShippingItemsMapper $shippingItemsInformationMapper
    ) {
        $this->multishippingModel = $multishippingModel;
        $this->shippingItemsInformationMapper = $shippingItemsInformationMapper;
    }

    /**
     * @inheritdoc
     */
    public function execute(ContextInterface $context, CartInterface $cart, array $shippingAddresses): void
    {
        if (count($shippingAddresses) === 1) {
            return;
        }
        if ((!$context->getUserId()) || $context->getUserType() == UserContextInterface::USER_TYPE_GUEST) {
            throw new GraphQlAuthorizationException(
                __(
                    'Multishipping allowed only for authorized customers'
                )
            );
        }

        $shippingItemsInformation = [];
        foreach ($shippingAddresses as $shippingAddress) {
            $customerAddressId = $shippingAddress['customer_address_id'] ?? null;
            $cartItems = $shippingAddress['cart_items'] ?? null;
            if (!$customerAddressId) {
                throw new GraphQlInputException(__('Parameter "customer_address_id" is required for multishipping'));
            }
            if (!$cartItems) {
                throw new GraphQlInputException(__('Parameter "cart_items" is required for multishipping'));
            }

            $shippingItemsInformation = array_merge(
                $shippingItemsInformation,
                $this->shippingItemsInformationMapper->map($shippingAddress)
            );
        }

        $this->multishippingModel->getCheckoutSession()->replaceQuote($cart);
        $this->multishippingModel->setShippingItemsInformation($shippingItemsInformation);
    }
}
