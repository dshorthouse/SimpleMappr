<?php

use Phinx\Migration\AbstractMigration;

class HashUser extends AbstractMigration
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
    public function up()
    {
        $table = $this->table('users');
        $table->addColumn('hash', 'string', array('limit' => 60, 'after' => 'uid'))
              ->save();

        $rows = $this->fetchAll('SELECT * FROM users');
        foreach($rows as $row) {
            $hash = password_hash($row['identifier'], PASSWORD_DEFAULT);
            $this->execute(sprintf("UPDATE users set hash = '%s' WHERE uid = %d", $hash, $row['uid']));
        }
        
        $table->addIndex(array('hash'), array('unique' => true, 'name' => 'idx_users_hash'))
              ->save();
    }

    public function down()
    {
        $table = $this->table('users');
        $table->removeColumn('hash');
    }
}
