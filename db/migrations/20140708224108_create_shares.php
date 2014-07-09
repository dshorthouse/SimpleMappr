<?php

use Phinx\Migration\AbstractMigration;

class CreateShares extends AbstractMigration
{   
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('shares');
        $table->addColumn('mid', 'integer')
              ->addColumn('created', 'integer')
              ->addIndex(array("mid"))
              ->addIndex(array("created"))
              ->create();
        $table->renameColumn("id", "sid");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}