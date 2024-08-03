<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240802184604 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE usuario (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE aposta DROP FOREIGN KEY fk_aposta_bolao');
        $this->addSql('ALTER TABLE aposta CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE conferida conferida TINYINT(1) DEFAULT 0 NOT NULL, CHANGE dezenas dezenas LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CFAC41F3D17F50A6 ON aposta (uuid)');
        $this->addSql('DROP INDEX fk_aposta_bolao_idx ON aposta');
        $this->addSql('CREATE INDEX IDX_CFAC41F3AB332E08 ON aposta (bolao_id)');
        $this->addSql('ALTER TABLE aposta ADD CONSTRAINT fk_aposta_bolao FOREIGN KEY (bolao_id) REFERENCES bolao (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE arquivo CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE bolao DROP FOREIGN KEY fk_bolao_concurso');
        $this->addSql('ALTER TABLE bolao CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('DROP INDEX fk_bolao_concurso_idx ON bolao');
        $this->addSql('CREATE INDEX IDX_A6B3200EF415D168 ON bolao (concurso_id)');
        $this->addSql('ALTER TABLE bolao ADD CONSTRAINT fk_bolao_concurso FOREIGN KEY (concurso_id) REFERENCES concurso (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE bolao_arquivo DROP FOREIGN KEY fk_bolao_arquivo_bolao');
        $this->addSql('ALTER TABLE bolao_arquivo DROP FOREIGN KEY fk_bolao_arquivo_arquivo');
        $this->addSql('ALTER TABLE bolao_arquivo CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('DROP INDEX fk_bolao_arquivo_bolao_idx ON bolao_arquivo');
        $this->addSql('CREATE INDEX IDX_403C3E1CAB332E08 ON bolao_arquivo (bolao_id)');
        $this->addSql('DROP INDEX fk_bolao_arquivo_arquivo_idx ON bolao_arquivo');
        $this->addSql('CREATE INDEX IDX_403C3E1C7E7C3263 ON bolao_arquivo (arquivo_id)');
        $this->addSql('ALTER TABLE bolao_arquivo ADD CONSTRAINT fk_bolao_arquivo_bolao FOREIGN KEY (bolao_id) REFERENCES bolao (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE bolao_arquivo ADD CONSTRAINT fk_bolao_arquivo_arquivo FOREIGN KEY (arquivo_id) REFERENCES arquivo (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE concurso DROP FOREIGN KEY fk_concurso_loteria');
        $this->addSql('ALTER TABLE concurso CHANGE apuracao apuracao DATE DEFAULT NULL, CHANGE municipio municipio VARCHAR(60) DEFAULT NULL, CHANGE local local VARCHAR(60) DEFAULT NULL, CHANGE uf uf VARCHAR(2) DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE rateio_premio rateio_premio LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE dezenas dezenas LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_785F9DE6D17F50A6 ON concurso (uuid)');
        $this->addSql('DROP INDEX fk_concurso_loteria_idx ON concurso');
        $this->addSql('CREATE INDEX IDX_785F9DE6924D0B67 ON concurso (loteria_id)');
        $this->addSql('ALTER TABLE concurso ADD CONSTRAINT fk_concurso_loteria FOREIGN KEY (loteria_id) REFERENCES loteria (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE loteria CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE aposta aposta LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', CHANGE dezenas dezenas LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('DROP INDEX slug_url_unique ON loteria');
        $this->addSql('CREATE UNIQUE INDEX slug_UNIQUE ON loteria (slug_url)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE usuario');
        $this->addSql('ALTER TABLE arquivo CHANGE uuid uuid BINARY(16) NOT NULL, CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE bolao_arquivo DROP FOREIGN KEY FK_403C3E1CAB332E08');
        $this->addSql('ALTER TABLE bolao_arquivo DROP FOREIGN KEY FK_403C3E1C7E7C3263');
        $this->addSql('ALTER TABLE bolao_arquivo CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('DROP INDEX idx_403c3e1c7e7c3263 ON bolao_arquivo');
        $this->addSql('CREATE INDEX fk_bolao_arquivo_arquivo_idx ON bolao_arquivo (arquivo_id)');
        $this->addSql('DROP INDEX idx_403c3e1cab332e08 ON bolao_arquivo');
        $this->addSql('CREATE INDEX fk_bolao_arquivo_bolao_idx ON bolao_arquivo (bolao_id)');
        $this->addSql('ALTER TABLE bolao_arquivo ADD CONSTRAINT FK_403C3E1CAB332E08 FOREIGN KEY (bolao_id) REFERENCES bolao (id)');
        $this->addSql('ALTER TABLE bolao_arquivo ADD CONSTRAINT FK_403C3E1C7E7C3263 FOREIGN KEY (arquivo_id) REFERENCES arquivo (id)');
        $this->addSql('ALTER TABLE bolao DROP FOREIGN KEY FK_A6B3200EF415D168');
        $this->addSql('ALTER TABLE bolao CHANGE uuid uuid BINARY(16) NOT NULL, CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('DROP INDEX idx_a6b3200ef415d168 ON bolao');
        $this->addSql('CREATE INDEX fk_bolao_concurso_idx ON bolao (concurso_id)');
        $this->addSql('ALTER TABLE bolao ADD CONSTRAINT FK_A6B3200EF415D168 FOREIGN KEY (concurso_id) REFERENCES concurso (id)');
        $this->addSql('DROP INDEX UNIQ_785F9DE6D17F50A6 ON concurso');
        $this->addSql('ALTER TABLE concurso DROP FOREIGN KEY FK_785F9DE6924D0B67');
        $this->addSql('ALTER TABLE concurso CHANGE apuracao apuracao DATE DEFAULT \'NULL\', CHANGE local local VARCHAR(60) DEFAULT \'NULL\', CHANGE municipio municipio VARCHAR(60) DEFAULT \'NULL\', CHANGE uf uf VARCHAR(2) DEFAULT \'NULL\', CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\', CHANGE uuid uuid BINARY(16) NOT NULL, CHANGE rateio_premio rateio_premio LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE dezenas dezenas LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('DROP INDEX idx_785f9de6924d0b67 ON concurso');
        $this->addSql('CREATE INDEX fk_concurso_loteria_idx ON concurso (loteria_id)');
        $this->addSql('ALTER TABLE concurso ADD CONSTRAINT FK_785F9DE6924D0B67 FOREIGN KEY (loteria_id) REFERENCES loteria (id)');
        $this->addSql('ALTER TABLE loteria CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\', CHANGE uuid uuid BINARY(16) NOT NULL, CHANGE aposta aposta LONGTEXT NOT NULL COLLATE `utf8mb4_bin`, CHANGE dezenas dezenas LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('DROP INDEX slug_unique ON loteria');
        $this->addSql('CREATE UNIQUE INDEX slug_url_UNIQUE ON loteria (slug_url)');
        $this->addSql('DROP INDEX UNIQ_CFAC41F3D17F50A6 ON aposta');
        $this->addSql('ALTER TABLE aposta DROP FOREIGN KEY FK_CFAC41F3AB332E08');
        $this->addSql('ALTER TABLE aposta CHANGE dezenas dezenas LONGTEXT NOT NULL COLLATE `utf8mb4_bin`, CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\', CHANGE uuid uuid BINARY(16) NOT NULL, CHANGE conferida conferida TINYINT(1) DEFAULT 0');
        $this->addSql('DROP INDEX idx_cfac41f3ab332e08 ON aposta');
        $this->addSql('CREATE INDEX fk_aposta_bolao_idx ON aposta (bolao_id)');
        $this->addSql('ALTER TABLE aposta ADD CONSTRAINT FK_CFAC41F3AB332E08 FOREIGN KEY (bolao_id) REFERENCES bolao (id)');
    }
}
