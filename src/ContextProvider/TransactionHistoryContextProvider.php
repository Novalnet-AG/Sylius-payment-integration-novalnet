<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\ContextProvider;

use Novalnet\SyliusNovalnetPaymentPlugin\Context\Admin\AdminUserContextInterface;
use Sylius\Bundle\UiBundle\ContextProvider\ContextProviderInterface;
use Sylius\Bundle\UiBundle\Registry\TemplateBlock;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class TransactionHistoryContextProvider implements ContextProviderInterface
{
    public function __construct(
        private RepositoryInterface $transactionHistory,
        private AdminUserContextInterface $adminUserContext,
    ) {
    }

    public function provide(array $templateContext, TemplateBlock $templateBlock): array
    {
        if (!isset($templateContext['order'])) {
            return $templateContext;
        }

        /** @var OrderInterface $order */
        $order = $templateContext['order'];

        $findData = [
            'order_id' => $order->getId(),
        ];

        $adminUser = $this->adminUserContext->getAdminUser();
        if (null === $adminUser) {
            $findData['private'] = false;
        }

        $notes = $this->transactionHistory->findBy($findData, ['id' => 'DESC']);

        if (null !== $notes) {
            $templateContext['novalnetTxnHistory'] = $notes;
        }

        return $templateContext;
    }

    public function supports(TemplateBlock $templateBlock): bool
    {
        return ('sylius.shop.account.order.show.subcontent' === $templateBlock->getEventName() || 'sylius.admin.order.show.sidebar' === $templateBlock->getEventName()) &&
            'transaction_history' === $templateBlock->getName();
    }
}
