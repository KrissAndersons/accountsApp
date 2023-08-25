<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230824131443 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, account_from_id INT NOT NULL, account_to_id INT NOT NULL, currency_from_id INT NOT NULL, currency_to_id INT NOT NULL, rate_from NUMERIC(19, 9) NOT NULL, rate_to NUMERIC(19, 9) NOT NULL, amount_from BIGINT NOT NULL, amount_to BIGINT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_723705D1B1E5CD43 (account_from_id), INDEX IDX_723705D16BA9314 (account_to_id), INDEX IDX_723705D1A56723E4 (currency_from_id), INDEX IDX_723705D167D74803 (currency_to_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1B1E5CD43 FOREIGN KEY (account_from_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D16BA9314 FOREIGN KEY (account_to_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1A56723E4 FOREIGN KEY (currency_from_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D167D74803 FOREIGN KEY (currency_to_id) REFERENCES currency (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1B1E5CD43');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D16BA9314');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1A56723E4');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D167D74803');
        $this->addSql('DROP TABLE transaction');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
