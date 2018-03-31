<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180314233633 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE truck DROP FOREIGN KEY FK_CDCCF30ACDC5AFD0');
        $this->addSql('DROP TABLE truck_owner');
        $this->addSql('DROP INDEX IDX_CDCCF30ACDC5AFD0 ON truck');
        $this->addSql('ALTER TABLE truck DROP truckowner_id');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE truck_owner (truckowner_id INT AUTO_INCREMENT NOT NULL, fname VARCHAR(20) NOT NULL COLLATE utf8_unicode_ci, lname VARCHAR(20) NOT NULL COLLATE utf8_unicode_ci, oname VARCHAR(20) DEFAULT NULL COLLATE utf8_unicode_ci, title VARCHAR(15) DEFAULT NULL COLLATE utf8_unicode_ci, PRIMARY KEY(truckowner_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE truck ADD truckowner_id INT NOT NULL');
        $this->addSql('ALTER TABLE truck ADD CONSTRAINT FK_CDCCF30ACDC5AFD0 FOREIGN KEY (truckowner_id) REFERENCES truck_owner (truckowner_id)');
        $this->addSql('CREATE INDEX IDX_CDCCF30ACDC5AFD0 ON truck (truckowner_id)');
    }
}
