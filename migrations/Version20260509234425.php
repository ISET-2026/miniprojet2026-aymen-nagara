<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260509234425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema for RecipeHub entities';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE CategorieRecette (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, icone VARCHAR(10) DEFAULT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX uniq_categorie_nom (nom), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE Ingredient (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, quantite VARCHAR(50) NOT NULL, recette_id INT NOT NULL, INDEX IDX_24F27BA089312FE9 (recette_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE Recette (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, instructions LONGTEXT NOT NULL, tempsPreparation INT NOT NULL, tempsCuisson INT DEFAULT NULL, difficulte VARCHAR(20) NOT NULL, nbPersonnes INT NOT NULL, dateCreation DATETIME NOT NULL, publiee TINYINT NOT NULL, imageName VARCHAR(255) DEFAULT NULL, categorie_id INT NOT NULL, auteur_id INT NOT NULL, INDEX IDX_86065A0CBCF5E72D (categorie_id), INDEX IDX_86065A0C60BB6FE6 (auteur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE recette_tagrecette (recette_id INT NOT NULL, tagrecette_id INT NOT NULL, INDEX IDX_25A6F4E689312FE9 (recette_id), INDEX IDX_25A6F4E6D59AB4D3 (tagrecette_id), PRIMARY KEY (recette_id, tagrecette_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE TagRecette (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, couleur VARCHAR(7) NOT NULL, UNIQUE INDEX uniq_tag_nom (nom), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, pseudo VARCHAR(50) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX uniq_user_email (email), UNIQUE INDEX uniq_user_pseudo (pseudo), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE Ingredient ADD CONSTRAINT FK_24F27BA089312FE9 FOREIGN KEY (recette_id) REFERENCES Recette (id)');
        $this->addSql('ALTER TABLE Recette ADD CONSTRAINT FK_86065A0CBCF5E72D FOREIGN KEY (categorie_id) REFERENCES CategorieRecette (id)');
        $this->addSql('ALTER TABLE Recette ADD CONSTRAINT FK_86065A0C60BB6FE6 FOREIGN KEY (auteur_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE recette_tagrecette ADD CONSTRAINT FK_25A6F4E689312FE9 FOREIGN KEY (recette_id) REFERENCES Recette (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recette_tagrecette ADD CONSTRAINT FK_25A6F4E6D59AB4D3 FOREIGN KEY (tagrecette_id) REFERENCES TagRecette (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Ingredient DROP FOREIGN KEY FK_24F27BA089312FE9');
        $this->addSql('ALTER TABLE Recette DROP FOREIGN KEY FK_86065A0CBCF5E72D');
        $this->addSql('ALTER TABLE Recette DROP FOREIGN KEY FK_86065A0C60BB6FE6');
        $this->addSql('ALTER TABLE recette_tagrecette DROP FOREIGN KEY FK_25A6F4E689312FE9');
        $this->addSql('ALTER TABLE recette_tagrecette DROP FOREIGN KEY FK_25A6F4E6D59AB4D3');
        $this->addSql('DROP TABLE Ingredient');
        $this->addSql('DROP TABLE Recette');
        $this->addSql('DROP TABLE recette_tagrecette');
        $this->addSql('DROP TABLE TagRecette');
        $this->addSql('DROP TABLE CategorieRecette');
        $this->addSql('DROP TABLE `user`');
    }
}
