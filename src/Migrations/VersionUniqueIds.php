<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\Migrations\AbstractMigration;

final class VersionUniqueIds extends AbstractMigration {

    private const UNIQUE_ID_NAME = 'UNIQUE_ID';

    public function getDescription(): string {
        return '';
    }

    public function preUp(Schema $schema): void {
        parent::preUp($schema);
    }

    public function up(Schema $schema): void {
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        $table = new Table('bib');
        $tables = $schema->getTables();
        $auxSchema = clone $schema;
        //get all foreing keys for deletion and recreation
        $addFKs = [];
        foreach ($tables as &$table) {
            $auxTable = $auxSchema->getTable($table->getName());
            $fks = $table->getForeignKeys();
            $addFKs[$table->getName()] = $fks;
            foreach ($fks as $fk) {
                $auxTable->removeForeignKey($fk->getName());
            }
        }

        $dropFKs = $schema->getMigrateToSql($auxSchema, $this->platform);
        $this->addSql('-- Remove Foreign Keys');
        foreach ($dropFKs as $dropFK) {
            $this->addSql($dropFK);
        }
        $aux2Schema = clone $auxSchema;
        $tables = $aux2Schema->getTables();
        $this->addSql('-- Migrar Primary Keys 1/2 (Drop & Add)');
        $tablesToPopulate = [];
        foreach ($tables as &$table) {
            if ($table->getName() == 'migration_versions') {
                continue;
            }
            try {
                $pks = $table->getPrimaryKeyColumns();
            } catch (\Doctrine\DBAL\DBALException $exc) {
                $pks = [];
            }
            if (count($pks) >= 1) {
                $table->addUniqueIndex($pks);
            }
            if (!$table->hasColumn(VersionUniqueIds::UNIQUE_ID_NAME)) {
                $pk = $table->addColumn(VersionUniqueIds::UNIQUE_ID_NAME, 'guid');
                $tablesToPopulate[$table->getName()] = $pk;
            }
        }
        $migratePKs = $aux2Schema->getMigrateFromSql($auxSchema, $this->platform);
        foreach ($migratePKs as $migratePK) {
            $this->addSql($migratePK);
        }
        $this->addSql('-- Populate Primary Keys');
        foreach ($tablesToPopulate as $table => $pk) {
            if ($auxSchema->getTable($table)->getPrimaryKey() !== null) {
                $this->addSql('ALTER TABLE ' . $table . ' DROP PRIMARY KEY');
            }
            $this->addSql('UPDATE ' . $table . ' SET ' . $pk->getName() . ' = (SELECT UUID())');
            $this->addSql('ALTER TABLE ' . $table . ' ADD PRIMARY KEY (' . $pk->getName() . ')');
        }
        $this->addSql('-- Recreate Foreign Keys');
        $aux3Schema = clone $aux2Schema;
        $tables = $aux3Schema->getTables();
        foreach ($tables as &$table) {
            if ($table->getName() == 'migration_versions') {
                continue;
            }
            if (!isset($addFKs[$table->getName()])) {
                continue;
            }
            $addFKs[$table->getName()];
            foreach ($addFKs[$table->getName()] as $fk) {
                $foreignTable = $aux3Schema->getTable($fk->getForeignTableName());
                $foreingColumns = $fk->getForeignColumns();
                $colmatch= array_combine($foreingColumns, $fk->getColumns());
                $idxs = $foreignTable->getIndexes();
                $fCols=[];
                foreach ($idxs as $idx) {
                    $idx->getColumns();
                    $diff = array_diff($foreingColumns, $idx->getColumns());
                    if ($diff == []) {
                        $fCols=$idx->getColumns();
                    }
                }
                $lCols=[];
                foreach ($fCols as $fCol) {
                    $lCols[]=$colmatch[$fCol];
                }
                $table->addForeignKeyConstraint($fk->getForeignTableName(), $lCols, $fCols, $fk->getOptions(), $fk->getName());
            }
        }
        $addFKs = $aux3Schema->getMigrateFromSql($aux2Schema, $this->platform);
        foreach ($addFKs as $addFK) {
            $this->addSql($addFK);
        }
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function postUp(Schema $schema): void {
        parent::postUp($schema);
    }

    public function down(Schema $schema): void {
        
    }

}
