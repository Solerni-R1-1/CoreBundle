<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/23 04:05:30
 */
class Version20140623160529 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_user_mooc_session (
                user_id NUMBER(10) NOT NULL, 
                moocsession_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(user_id, moocsession_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_D4EF03A0A76ED395 ON claro_user_mooc_session (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D4EF03A0D25A0E37 ON claro_user_mooc_session (moocsession_id)
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_session (
                id NUMBER(10) NOT NULL, 
                mooc_id NUMBER(10) NOT NULL, 
                start_date TIMESTAMP(0) NOT NULL, 
                end_date TIMESTAMP(0) NOT NULL, 
                start_inscription_date TIMESTAMP(0) NOT NULL, 
                end_inscription_date TIMESTAMP(0) NOT NULL, 
                title VARCHAR2(255) NOT NULL, 
                max_users NUMBER(10) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_MOOC_SESSION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_MOOC_SESSION ADD CONSTRAINT CLARO_MOOC_SESSION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_MOOC_SESSION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_MOOC_SESSION_AI_PK BEFORE INSERT ON CLARO_MOOC_SESSION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_MOOC_SESSION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_MOOC_SESSION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_MOOC_SESSION_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_MOOC_SESSION_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_B0164DD4255EEB87 ON claro_mooc_session (mooc_id)
        ");
        $this->addSql("
            CREATE TABLE claro_mooc (
                id NUMBER(10) NOT NULL, 
                workspace_id NUMBER(10) DEFAULT NULL, 
                title VARCHAR2(255) NOT NULL, 
                alias VARCHAR2(255) NOT NULL, 
                description CLOB NOT NULL, 
                desc_img VARCHAR2(255) NOT NULL, 
                post_end_action NUMBER(10) NOT NULL, 
                is_public NUMBER(1) NOT NULL, 
                duration VARCHAR2(255) NOT NULL, 
                weekly_time VARCHAR2(255) NOT NULL, 
                cost NUMBER(10) NOT NULL, 
                language VARCHAR2(10) NOT NULL, 
                has_video NUMBER(1) NOT NULL, 
                has_subtitle NUMBER(1) NOT NULL, 
                prerequisites CLOB NOT NULL, 
                team_description CLOB NOT NULL, 
                has_facebook_share NUMBER(1) NOT NULL, 
                has_tweeter_share NUMBER(1) NOT NULL, 
                has_gplus_share NUMBER(1) NOT NULL, 
                has_linkin_share NUMBER(1) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_MOOC' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_MOOC ADD CONSTRAINT CLARO_MOOC_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_MOOC_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_MOOC_AI_PK BEFORE INSERT ON CLARO_MOOC FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_MOOC_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_MOOC_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_MOOC_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_MOOC_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_FB43C54E82D40A1F ON claro_mooc (workspace_id)
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_session 
            ADD CONSTRAINT FK_D4EF03A0A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_user_mooc_session 
            ADD CONSTRAINT FK_D4EF03A0D25A0E37 FOREIGN KEY (moocsession_id) 
            REFERENCES claro_mooc_session (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session 
            ADD CONSTRAINT FK_B0164DD4255EEB87 FOREIGN KEY (mooc_id) 
            REFERENCES claro_mooc (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD CONSTRAINT FK_FB43C54E82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id)
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD (
                mooc_id NUMBER(10) DEFAULT NULL
            )
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD CONSTRAINT FK_D9028545255EEB87 FOREIGN KEY (mooc_id) 
            REFERENCES claro_mooc (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            CREATE INDEX IDX_D9028545255EEB87 ON claro_workspace (mooc_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user_mooc_session 
            DROP CONSTRAINT FK_D4EF03A0D25A0E37
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP CONSTRAINT FK_D9028545255EEB87
        ");
        $this->addSql("
            ALTER TABLE claro_mooc_session 
            DROP CONSTRAINT FK_B0164DD4255EEB87
        ");
        $this->addSql("
            DROP TABLE claro_user_mooc_session
        ");
        $this->addSql("
            DROP TABLE claro_mooc_session
        ");
        $this->addSql("
            DROP TABLE claro_mooc
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP (mooc_id)
        ");
        $this->addSql("
            DROP INDEX IDX_D9028545255EEB87
        ");
    }
}