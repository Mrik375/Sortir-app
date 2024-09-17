<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240917092629 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__lieu AS SELECT id, nom, rue, latitude, longitude FROM lieu');
        $this->addSql('DROP TABLE lieu');
        $this->addSql('CREATE TABLE lieu (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, lieu_ville_id INTEGER NOT NULL, nom VARCHAR(255) NOT NULL, rue VARCHAR(255) NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, CONSTRAINT FK_2F577D5970D356E0 FOREIGN KEY (lieu_ville_id) REFERENCES ville (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO lieu (id, nom, rue, latitude, longitude) SELECT id, nom, rue, latitude, longitude FROM __temp__lieu');
        $this->addSql('DROP TABLE __temp__lieu');
        $this->addSql('CREATE INDEX IDX_2F577D5970D356E0 ON lieu (lieu_ville_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__participant AS SELECT id, nom, prenom, telephone, mail, mot_passe, administrateur, actif FROM participant');
        $this->addSql('DROP TABLE participant');
        $this->addSql('CREATE TABLE participant (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, est_rattache_a_id INTEGER NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, telephone INTEGER NOT NULL, mail VARCHAR(255) NOT NULL, mot_passe VARCHAR(255) NOT NULL, administrateur BOOLEAN NOT NULL, actif BOOLEAN NOT NULL, CONSTRAINT FK_D79F6B11C8761AC1 FOREIGN KEY (est_rattache_a_id) REFERENCES campus (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO participant (id, nom, prenom, telephone, mail, mot_passe, administrateur, actif) SELECT id, nom, prenom, telephone, mail, mot_passe, administrateur, actif FROM __temp__participant');
        $this->addSql('DROP TABLE __temp__participant');
        $this->addSql('CREATE INDEX IDX_D79F6B11C8761AC1 ON participant (est_rattache_a_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__sortie AS SELECT id, site_organisateur_id, nom, date_heure_debut, duree, date_limite_inscription, bn_inscription_max, infos_sortie, etat FROM sortie');
        $this->addSql('DROP TABLE sortie');
        $this->addSql('CREATE TABLE sortie (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, site_organisateur_id INTEGER NOT NULL, organisateur_id INTEGER NOT NULL, etat_sortie_id INTEGER NOT NULL, lieu_sortie_id INTEGER NOT NULL, nom VARCHAR(255) NOT NULL, date_heure_debut DATETIME NOT NULL, duree INTEGER NOT NULL, date_limite_inscription DATETIME NOT NULL, bn_inscription_max INTEGER NOT NULL, infos_sortie CLOB NOT NULL, etat VARCHAR(255) NOT NULL, CONSTRAINT FK_3C3FD3F2D7AC6C11 FOREIGN KEY (site_organisateur_id) REFERENCES campus (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3C3FD3F2D936B2FA FOREIGN KEY (organisateur_id) REFERENCES participant (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3C3FD3F23CE09FBF FOREIGN KEY (etat_sortie_id) REFERENCES etat (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3C3FD3F2A31542E4 FOREIGN KEY (lieu_sortie_id) REFERENCES lieu (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO sortie (id, site_organisateur_id, nom, date_heure_debut, duree, date_limite_inscription, bn_inscription_max, infos_sortie, etat) SELECT id, site_organisateur_id, nom, date_heure_debut, duree, date_limite_inscription, bn_inscription_max, infos_sortie, etat FROM __temp__sortie');
        $this->addSql('DROP TABLE __temp__sortie');
        $this->addSql('CREATE INDEX IDX_3C3FD3F2D7AC6C11 ON sortie (site_organisateur_id)');
        $this->addSql('CREATE INDEX IDX_3C3FD3F2D936B2FA ON sortie (organisateur_id)');
        $this->addSql('CREATE INDEX IDX_3C3FD3F23CE09FBF ON sortie (etat_sortie_id)');
        $this->addSql('CREATE INDEX IDX_3C3FD3F2A31542E4 ON sortie (lieu_sortie_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__lieu AS SELECT id, nom, rue, latitude, longitude FROM lieu');
        $this->addSql('DROP TABLE lieu');
        $this->addSql('CREATE TABLE lieu (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, rue VARCHAR(255) NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL)');
        $this->addSql('INSERT INTO lieu (id, nom, rue, latitude, longitude) SELECT id, nom, rue, latitude, longitude FROM __temp__lieu');
        $this->addSql('DROP TABLE __temp__lieu');
        $this->addSql('CREATE TEMPORARY TABLE __temp__participant AS SELECT id, nom, prenom, telephone, mail, mot_passe, administrateur, actif FROM participant');
        $this->addSql('DROP TABLE participant');
        $this->addSql('CREATE TABLE participant (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, telephone INTEGER NOT NULL, mail VARCHAR(255) NOT NULL, mot_passe VARCHAR(255) NOT NULL, administrateur BOOLEAN NOT NULL, actif BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO participant (id, nom, prenom, telephone, mail, mot_passe, administrateur, actif) SELECT id, nom, prenom, telephone, mail, mot_passe, administrateur, actif FROM __temp__participant');
        $this->addSql('DROP TABLE __temp__participant');
        $this->addSql('CREATE TEMPORARY TABLE __temp__sortie AS SELECT id, site_organisateur_id, nom, date_heure_debut, duree, date_limite_inscription, bn_inscription_max, infos_sortie, etat FROM sortie');
        $this->addSql('DROP TABLE sortie');
        $this->addSql('CREATE TABLE sortie (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, site_organisateur_id INTEGER NOT NULL, nom VARCHAR(255) NOT NULL, date_heure_debut DATETIME NOT NULL, duree INTEGER NOT NULL, date_limite_inscription DATETIME NOT NULL, bn_inscription_max INTEGER NOT NULL, infos_sortie CLOB NOT NULL, etat VARCHAR(255) NOT NULL, CONSTRAINT FK_3C3FD3F2D7AC6C11 FOREIGN KEY (site_organisateur_id) REFERENCES campus (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO sortie (id, site_organisateur_id, nom, date_heure_debut, duree, date_limite_inscription, bn_inscription_max, infos_sortie, etat) SELECT id, site_organisateur_id, nom, date_heure_debut, duree, date_limite_inscription, bn_inscription_max, infos_sortie, etat FROM __temp__sortie');
        $this->addSql('DROP TABLE __temp__sortie');
        $this->addSql('CREATE INDEX IDX_3C3FD3F2D7AC6C11 ON sortie (site_organisateur_id)');
    }
}
