<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180313193914 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product_distribution ADD CONSTRAINT FK_29EC15EA8D9F6D38 FOREIGN KEY (order_id) REFERENCES salesorder (order_id)');
        $this->addSql('CREATE INDEX IDX_29EC15EA8D9F6D38 ON product_distribution (order_id)');
        $this->addSql('ALTER TABLE salesorder ADD quantity_delivered INT UNSIGNED NOT NULL, ADD order_delivery_status VARCHAR(20) DEFAULT \'not-delivered\' NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product_distribution DROP FOREIGN KEY FK_29EC15EA8D9F6D38');
        $this->addSql('DROP INDEX IDX_29EC15EA8D9F6D38 ON product_distribution');
        $this->addSql('ALTER TABLE salesorder DROP quantity_delivered, DROP order_delivery_status');
    }
}
