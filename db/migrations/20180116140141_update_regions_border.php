<?php

use Phinx\Migration\AbstractMigration;

class UpdateRegionsBorder extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    /**
     * Migrate Up.
     */
    public function up()
    {
      $rows = $this->fetchAll('SELECT mid, map FROM maps');
      foreach ($rows as $row) {
          $map = json_decode($row['map'], true);
          if (array_key_exists('regions', $map) && is_array($map['regions'])) {
              foreach ($map['regions'] as $key => $data) {
                  if (is_int($key)) {
                      $map['regions'][$key]['border'] = 'on';
                  }
              }
              $first_region = reset($map['regions']);
              if (!is_array($first_region)) {
                  $map['regions']['border'] = 'on';
                  $map['regions'] = [$map['regions']]; 
              }
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
      foreach ($rows as $row) {
          $map = json_decode($row['map'], true);
          if (array_key_exists('regions', $map) && is_array($map['regions'])) {
              foreach ($map['regions'] as $key => $data) {
                  if (is_int($key)) {
                      unset($map['regions'][$key]['border']);
                  }
              }
          }
          $map = json_encode($map);
          $this->execute(sprintf("UPDATE maps set map = '%s' WHERE mid = %d", addslashes($map), $row['mid']));
      }
    }
}
