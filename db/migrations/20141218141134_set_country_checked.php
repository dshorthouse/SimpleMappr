<?php

use Phinx\Migration\AbstractMigration;

class SetCountryChecked extends AbstractMigration
{
    
    /**
     * Migrate Up.
     */
    public function up()
    {
        $rows = $this->fetchAll('SELECT mid, map FROM maps');
        foreach($rows as $row) {
            $map = json_decode($row['map'], true);
            if(!is_array($map)) {
                continue;
            }
            if(array_key_exists('layers', $map)) {
                $map['layers']['countries'] = 'on';
            } else {
                $map['layers'] = array('countries' => 'on');
            }
            $map = json_encode($map);
            $this->execute(sprintf("UPDATE maps set map = '%s' WHERE mid = %d", addslashes($map), $row['mid']));
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $rows = $this->fetchAll('SELECT mid, map FROM maps');
        foreach($rows as $row) {
            $map = json_decode($row['map'], true);
            if(!is_array($map)) {
                continue;
            }
            if(array_key_exists('layers', $map)) {
                unset($map['layers']['countries']);
            } else {
                unset($map['layers']);
            }
            $map = json_encode($map);
            $this->execute(sprintf("UPDATE maps set map = '%s' WHERE mid = %d", addslashes($map), $row['mid']));
        }
    }
}