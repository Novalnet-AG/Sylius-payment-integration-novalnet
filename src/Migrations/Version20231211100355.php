<?php

declare(strict_types=1);

namespace Novalnet\SyliusNovalnetPaymentPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231211100355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Novalnet transactions and history table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE novalnet_transactions (id INT AUTO_INCREMENT NOT NULL, payment_id INT NOT NULL, order_id INT NOT NULL, tid BIGINT NOT NULL, payment_type VARCHAR(50) NOT NULL, amount INT NOT NULL, paid_amount INT NOT NULL, refund_amount INT NOT NULL, gateway_status VARCHAR(50) NOT NULL, details JSON DEFAULT NULL, currency VARCHAR(10) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE novalnet_transaction_history (id INT AUTO_INCREMENT NOT NULL, order_id INT NOT NULL, payment_id INT NOT NULL, note VARCHAR(1000) DEFAULT NULL, private TINYINT(1) DEFAULT NULL, date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE novalnet_transactions');
        $this->addSql('DROP TABLE novalnet_transaction_history');
    }
}
