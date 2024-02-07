<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Repository;

use Novalnet\SyliusNovalnetPaymentPlugin\Entity\NovalnetTransactionHistory;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

/**
 * @extends EntityRepository<NovalnetTransactionHistory>
 *
 * @method NovalnetTransactionHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method NovalnetTransactionHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method NovalnetTransactionHistory[]    findAll()
 * @method NovalnetTransactionHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NovalnetTransactionHistoryRepository extends EntityRepository
{
}
