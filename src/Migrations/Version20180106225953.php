<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180106225953 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE salestransaction (transaction_id INT AUTO_INCREMENT NOT NULL, order_id INT NOT NULL, committed_by INT NOT NULL, tid VARCHAR(15) NOT NULL, trans_date DATETIME NOT NULL, date_recorded DATETIME NOT NULL, amount_paid NUMERIC(15, 2) NOT NULL, payment_method VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_1E4042C552596C31 (tid), INDEX IDX_1E4042C58D9F6D38 (order_id), INDEX IDX_1E4042C5D554C27C (committed_by), PRIMARY KEY(transaction_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE salestransaction ADD CONSTRAINT FK_1E4042C58D9F6D38 FOREIGN KEY (order_id) REFERENCES salesorder (order_id)');
        $this->addSql('ALTER TABLE salestransaction ADD CONSTRAINT FK_1E4042C5D554C27C FOREIGN KEY (committed_by) REFERENCES user (user_id)');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('ALTER TABLE refund ADD CONSTRAINT FK_5B2C14588D9F6D38 FOREIGN KEY (order_id) REFERENCES salesorder (order_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE transaction (transaction_id INT AUTO_INCREMENT NOT NULL, committed_by INT NOT NULL, order_id INT NOT NULL, trans_date DATETIME NOT NULL, amount_paid NUMERIC(15, 2) NOT NULL, payment_method VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, date_recorded DATETIME NOT NULL, INDEX IDX_723705D18D9F6D38 (order_id), INDEX IDX_723705D1D554C27C (committed_by), PRIMARY KEY(transaction_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1D554C27C FOREIGN KEY (committed_by) REFERENCES user (user_id)');
        $this->addSql('DROP TABLE salestransaction');
        $this->addSql('ALTER TABLE refund DROP FOREIGN KEY FK_5B2C14588D9F6D38');
    }
}
