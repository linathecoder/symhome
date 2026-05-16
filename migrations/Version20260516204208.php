<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260516204208 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE meubles (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prix DOUBLE PRECISION NOT NULL, description LONGTEXT NOT NULL, stock INT NOT NULL, image VARCHAR(255) NOT NULL, categorie_id INT DEFAULT NULL, INDEX IDX_1F62B1A5BCF5E72D (categorie_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE meubles ADD CONSTRAINT FK_1F62B1A5BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE meuble DROP FOREIGN KEY `FK_B758BB86BCF5E72D`');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE meuble');
        $this->addSql('ALTER TABLE commande CHANGE status status VARCHAR(50) NOT NULL, CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY `FK_3170B74BE1780C00`');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74BE1780C00 FOREIGN KEY (meuble_id) REFERENCES meubles (id)');
        $this->addSql('ALTER TABLE user ADD nom VARCHAR(100) DEFAULT NULL, ADD prenom VARCHAR(100) DEFAULT NULL, ADD is_verified TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE meuble (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, prix DOUBLE PRECISION NOT NULL, description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, stock INT NOT NULL, image VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, categorie_id INT DEFAULT NULL, INDEX IDX_B758BB86BCF5E72D (categorie_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE meuble ADD CONSTRAINT `FK_B758BB86BCF5E72D` FOREIGN KEY (categorie_id) REFERENCES categorie (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE meubles DROP FOREIGN KEY FK_1F62B1A5BCF5E72D');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE meubles');
        $this->addSql('ALTER TABLE commande CHANGE status status VARCHAR(255) NOT NULL, CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74BE1780C00');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT `FK_3170B74BE1780C00` FOREIGN KEY (meuble_id) REFERENCES meuble (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user DROP nom, DROP prenom, DROP is_verified');
    }
}
