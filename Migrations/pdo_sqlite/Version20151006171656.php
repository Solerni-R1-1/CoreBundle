<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/10/06 05:17:10
 */
class Version20151006171656 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            ADD COLUMN lockedLogin BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD COLUMN lockedPassword BOOLEAN NOT NULL
        ");
        $this->addSql("
            DROP INDEX IDX_CEF67987255EEB87
        ");
        $this->addSql("
            DROP INDEX IDX_CEF67987A76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_user_mooc_preferences AS 
            SELECT mooc_id, 
            user_id, 
            visibility 
            FROM claro_user_mooc_preferences
        ");
        $this->addSql("
            DROP TABLE claro_user_mooc_preferences
        ");
        $this->addSql("
            CREATE TABLE claro_user_mooc_preferences (
                mooc_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                visibility BOOLEAN NOT NULL, 
                PRIMARY KEY(mooc_id, user_id), 
                CONSTRAINT FK_CEF67987255EEB87 FOREIGN KEY (mooc_id) 
                REFERENCES claro_mooc (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_CEF67987A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_user_mooc_preferences (mooc_id, user_id, visibility) 
            SELECT mooc_id, 
            user_id, 
            visibility 
            FROM __temp__claro_user_mooc_preferences
        ");
        $this->addSql("
            DROP TABLE __temp__claro_user_mooc_preferences
        ");
        $this->addSql("
            CREATE INDEX IDX_CEF67987255EEB87 ON claro_user_mooc_preferences (mooc_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CEF67987A76ED395 ON claro_user_mooc_preferences (user_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_EB8D2852F85E0677
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D28525126AC48
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D2852181F3A64
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D285282D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_user AS 
            SELECT id, 
            workspace_id, 
            first_name, 
            last_name, 
            username, 
            password, 
            locale, 
            salt, 
            phone, 
            mail, 
            administrative_code, 
            creation_date, 
            reset_password, 
            hash_time, 
            picture, 
            description, 
            hasAcceptedTerms, 
            is_enabled, 
            is_mail_notified, 
            last_uri, 
            has_accepted_com_terms, 
            public_url, 
            has_tuned_public_url, 
            is_first_visit, 
            is_validate, 
            key_validate, 
            is_facebook_account, 
            gender, 
            country, 
            city, 
            birthdate, 
            website, 
            facebook, 
            twitter, 
            linkedIn, 
            googlePlus 
            FROM claro_user
        ");
        $this->addSql("
            DROP TABLE claro_user
        ");
        $this->addSql("
            CREATE TABLE claro_user (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                first_name VARCHAR(50) NOT NULL, 
                last_name VARCHAR(50) NOT NULL, 
                username VARCHAR(255) NOT NULL, 
                password VARCHAR(255) NOT NULL, 
                locale VARCHAR(255) DEFAULT NULL, 
                salt VARCHAR(255) NOT NULL, 
                phone VARCHAR(255) DEFAULT NULL, 
                mail VARCHAR(255) NOT NULL, 
                administrative_code VARCHAR(255) DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                reset_password VARCHAR(255) DEFAULT NULL, 
                hash_time INTEGER DEFAULT NULL, 
                picture VARCHAR(255) DEFAULT NULL, 
                description CLOB DEFAULT NULL, 
                hasAcceptedTerms BOOLEAN DEFAULT NULL, 
                is_enabled BOOLEAN NOT NULL, 
                is_mail_notified BOOLEAN NOT NULL, 
                last_uri VARCHAR(255) DEFAULT NULL, 
                has_accepted_com_terms BOOLEAN DEFAULT NULL, 
                public_url VARCHAR(255) DEFAULT NULL, 
                has_tuned_public_url BOOLEAN NOT NULL, 
                is_first_visit BOOLEAN NOT NULL, 
                is_validate BOOLEAN NOT NULL, 
                key_validate VARCHAR(255) DEFAULT NULL, 
                is_facebook_account BOOLEAN DEFAULT NULL, 
                gender INTEGER NOT NULL, 
                country VARCHAR(255) DEFAULT NULL, 
                city VARCHAR(255) DEFAULT NULL, 
                birthdate DATE DEFAULT NULL, 
                website VARCHAR(255) DEFAULT NULL, 
                facebook VARCHAR(255) DEFAULT NULL, 
                twitter VARCHAR(255) DEFAULT NULL, 
                linkedIn VARCHAR(255) DEFAULT NULL, 
                googlePlus VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_EB8D285282D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_user (
                id, workspace_id, first_name, last_name, 
                username, password, locale, salt, 
                phone, mail, administrative_code, 
                creation_date, reset_password, hash_time, 
                picture, description, hasAcceptedTerms, 
                is_enabled, is_mail_notified, last_uri, 
                has_accepted_com_terms, public_url, 
                has_tuned_public_url, is_first_visit, 
                is_validate, key_validate, is_facebook_account, 
                gender, country, city, birthdate, 
                website, facebook, twitter, linkedIn, 
                googlePlus
            ) 
            SELECT id, 
            workspace_id, 
            first_name, 
            last_name, 
            username, 
            password, 
            locale, 
            salt, 
            phone, 
            mail, 
            administrative_code, 
            creation_date, 
            reset_password, 
            hash_time, 
            picture, 
            description, 
            hasAcceptedTerms, 
            is_enabled, 
            is_mail_notified, 
            last_uri, 
            has_accepted_com_terms, 
            public_url, 
            has_tuned_public_url, 
            is_first_visit, 
            is_validate, 
            key_validate, 
            is_facebook_account, 
            gender, 
            country, 
            city, 
            birthdate, 
            website, 
            facebook, 
            twitter, 
            linkedIn, 
            googlePlus 
            FROM __temp__claro_user
        ");
        $this->addSql("
            DROP TABLE __temp__claro_user
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D2852F85E0677 ON claro_user (username)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D28525126AC48 ON claro_user (mail)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D2852181F3A64 ON claro_user (public_url)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D285282D40A1F ON claro_user (workspace_id)
        ");
        $this->addSql("
            DROP INDEX IDX_CEF67987255EEB87
        ");
        $this->addSql("
            DROP INDEX IDX_CEF67987A76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_user_mooc_preferences AS 
            SELECT mooc_id, 
            user_id, 
            visibility 
            FROM claro_user_mooc_preferences
        ");
        $this->addSql("
            DROP TABLE claro_user_mooc_preferences
        ");
        $this->addSql("
            CREATE TABLE claro_user_mooc_preferences (
                mooc_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                visibility INTEGER NOT NULL, 
                PRIMARY KEY(mooc_id, user_id), 
                CONSTRAINT FK_CEF67987255EEB87 FOREIGN KEY (mooc_id) 
                REFERENCES claro_mooc (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_CEF67987A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_user_mooc_preferences (mooc_id, user_id, visibility) 
            SELECT mooc_id, 
            user_id, 
            visibility 
            FROM __temp__claro_user_mooc_preferences
        ");
        $this->addSql("
            DROP TABLE __temp__claro_user_mooc_preferences
        ");
        $this->addSql("
            CREATE INDEX IDX_CEF67987255EEB87 ON claro_user_mooc_preferences (mooc_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_CEF67987A76ED395 ON claro_user_mooc_preferences (user_id)
        ");
    }
}