<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180321183333 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE truck_expense (id INT AUTO_INCREMENT NOT NULL, truck_id INT NOT NULL, entered_by INT NOT NULL, date_of_expense DATETIME NOT NULL, date_recorded DATETIME NOT NULL, amount NUMERIC(15, 2) NOT NULL, purpose VARCHAR(500) NOT NULL, INDEX IDX_EA8BA2F2C6957CCE (truck_id), INDEX IDX_EA8BA2F2BFF9CCF (entered_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE truck_expense ADD CONSTRAINT FK_EA8BA2F2C6957CCE FOREIGN KEY (truck_id) REFERENCES truck (truck_id)');
        $this->addSql('ALTER TABLE truck_expense ADD CONSTRAINT FK_EA8BA2F2BFF9CCF FOREIGN KEY (entered_by) REFERENCES user (user_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE truck_expense');
    }
}
