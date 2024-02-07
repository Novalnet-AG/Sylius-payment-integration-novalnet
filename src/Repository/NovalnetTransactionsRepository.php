<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Repository;

use Novalnet\SyliusNovalnetPaymentPlugin\Entity\NovalnetTransactions;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

/**
 * @extends EntityRepository<NovalnetTransactions>
 *
 * @method NovalnetTransactions|null find($id, $lockMode = null, $lockVersion = null)
 * @method NovalnetTransactions|null findOneBy(array $criteria, array $orderBy = null)
 * @method NovalnetTransactions[]    findAll()
 * @method NovalnetTransactions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NovalnetTransactionsRepository extends EntityRepository
{
}
