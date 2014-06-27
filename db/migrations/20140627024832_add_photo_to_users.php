<?php

use Phinx\Migration\AbstractMigration;

class AddPhotoToUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('users');
        $table->addColumn('photo', 'string', array('after' => 'email'))
                      ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('users');
        $table->removeColumn('photo');
    }
}