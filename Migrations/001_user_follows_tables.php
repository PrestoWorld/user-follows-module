<?php

declare(strict_types=1);

use Cycle\Database\DatabaseInterface;
use Cycle\Database\Schema\AbstractTable;
use PrestoWorld\Database\MigrationInterface;

return new class implements MigrationInterface {
    public function up(DatabaseInterface $db, string $prefix): void
    {
        $this->userFollows($db, $prefix);
    }

    public function down(DatabaseInterface $db, string $prefix): void
    {
        $tables = ['user_follows'];

        foreach ($tables as $name) {
            $full = $prefix . $name;
            if ($db->hasTable($full)) {
                $schema = $this->schema($db, $full);
                $schema->declareDropped();
                $schema->save();
            }
        }
    }

    private function userFollows(DatabaseInterface $db, string $prefix): void
    {
        $table = $this->table($db, $prefix, 'user_follows');
        $table->primary('id');
        $table->column('external_id')->string(64);
        $table->column('business_id')->string(64);
        $table->column('follower_id')->string(64);
        $table->column('following_id')->string(64);
        $table->column('created_at')->bigInteger()->defaultValue(0);
        $table->index(['external_id'])->unique();
        $table->index(['business_id', 'follower_id', 'following_id'])->unique();
        $table->index(['business_id', 'following_id']);
        $table->index(['business_id', 'follower_id']);
        $table->save();
    }

    private function table(DatabaseInterface $db, string $prefix, string $name): AbstractTable
    {
        return $this->schema($db, $prefix . $name);
    }

    private function schema(DatabaseInterface $db, string $name): AbstractTable
    {
        $table = $db->table($name);
        if (!$table instanceof \Cycle\Database\Table) {
            throw new \RuntimeException("Expected concrete Cycle table for {$name}, got " . $table::class);
        }
        return $table->getSchema();
    }
};
