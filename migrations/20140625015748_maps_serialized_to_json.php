<?php

use Phinx\Migration\AbstractMigration;

class MapsSerializedToJson extends AbstractMigration
{
    
    /**
     * Migrate Up.
     */
    public function up()
    {
        $rows = $this->fetchAll('SELECT mid, map FROM maps');
        foreach($rows as $row) {
            $map = json_encode(@unserialize($row['map']));
            $this->execute(sprintf("UPDATE maps set map = '%s' WHERE mid=%d", addslashes($map), $row['mid']));
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $rows = $this->fetchAll('SELECT mid, map FROM maps');
        foreach($rows as $row) {
            $map = serialize(json_decode($row['map']));
            $this->execute(sprintf("UPDATE maps set map = '%s' WHERE mid=%d", addslashes($map), $row['mid']));
        }
    }
}