<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/10 11:38:21
 */
class Version20140710113819 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_mooc_access_constraints (
                id NUMBER(10) NOT NULL, 
                name VARCHAR2(255) NOT NULL, 
                whitelist CLOB DEFAULT NULL, 
                patterns CLOB DEFAULT NULL, 
                moocOwner_id NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_MOOC_ACCESS_CONSTRAINTS' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_MOOC_ACCESS_CONSTRAINTS ADD CONSTRAINT CLARO_MOOC_ACCESS_CONSTRAINTS_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_MOOC_ACCESS_CONSTRAINTS_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_MOOC_ACCESS_CONSTRAINTS_AI_PK BEFORE INSERT ON CLARO_MOOC_ACCESS_CONSTRAINTS FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_MOOC_ACCESS_CONSTRAINTS_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_MOOC_ACCESS_CONSTRAINTS_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_MOOC_ACCESS_CONSTRAINTS_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_MOOC_ACCESS_CONSTRAINTS_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_8C229ACDA96EF72D ON claro_mooc_access_constraints (moocOwner_id)
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_constraints_to_users (
                moocaccessconstraints_id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(
                    moocaccessconstraints_id, user_id
                )
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_8DECBE50DDA4386D ON claro_mooc_constraints_to_users (moocaccessconstraints_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_8DECBE50A76ED395 ON claro_mooc_constraints_to_users (user_id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_access_constraints 
            ADD CONSTRAINT FK_8C229ACDA96EF72D FOREIGN KEY (moocOwner_id) 
            REFERENCES claro_mooc_owner (id)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_constraints_to_users 
            ADD CONSTRAINT FK_8DECBE50DDA4386D FOREIGN KEY (moocaccessconstraints_id) 
            REFERENCES claro_mooc_access_constraints (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_constraints_to_users 
            ADD CONSTRAINT FK_8DECBE50A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_owner MODIFY (
                name VARCHAR2(255) NOT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc_constraints_to_users 
            DROP CONSTRAINT FK_8DECBE50DDA4386D
        ");
        $this->addSql("
            DROP TABLE claro_mooc_access_constraints
        ");
        $this->addSql("
            DROP TABLE claro_mooc_constraints_to_users
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_owner MODIFY (
                name VARCHAR2(255) DEFAULT NULL
            )
        ");
    }
}