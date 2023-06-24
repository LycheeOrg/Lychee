<?php

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

return new class extends Migration
{
	public const ACCESS_PERMISSIONS = 'access_permissions';

	// Id names
	public const BASE_ALBUM_ID = 'base_album_id';
	public const USER_ID = 'user_id';

	// Attributes name
	public const IS_LINK_REQUIRED = 'is_link_required';
	// public const GRANTS_FULL_PHOTO_ACCESS = 'grants_full_photo_access';
	// public const GRANTS_DOWNLOAD = 'grants_download';
	// public const GRANTS_UPLOAD = 'grants_upload';
	// public const GRANTS_EDIT = 'grants_edit';
	// public const GRANTS_DELETE = 'grants_delete';
	public const PASSWORD = 'password';

	private string $driverName;
	private AbstractSchemaManager $schemaManager;
	private ConsoleOutput $output;
	/** @var ProgressBar[] */
	private array $progressBars;
	private ConsoleSectionOutput $msgSection;
	/**
	 * @throws DBALException
	 */
	public function __construct()
	{
		$connection = Schema::connection(null)->getConnection();
		$this->driverName = $connection->getDriverName();
		$this->schemaManager = $connection->getDoctrineSchemaManager();
		$this->output = new ConsoleOutput();
		$this->progressBars = [];
		$this->msgSection = $this->output->section();
	}

    /**
     * Run the migrations.
     */
    public function up(): void
    {
		Schema::table(self::ACCESS_PERMISSIONS, function (Blueprint $table) {
            $table->index([self::IS_LINK_REQUIRED]); // for albums which don't require a direct link and are public
            // $table->index([self::USER_ID]); // for albums which are own by the currently authenticated user
            $table->index([self::IS_LINK_REQUIRED, self::PASSWORD]); // for albums which are public and how no password
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		Schema::table(self::ACCESS_PERMISSIONS, function (Blueprint $table) {
			$this->dropIndexIfExists($table, self::ACCESS_PERMISSIONS . '_' . self::IS_LINK_REQUIRED . '_index');
			$this->dropIndexIfExists($table, self::ACCESS_PERMISSIONS . '_' . self::IS_LINK_REQUIRED . '_'. self::PASSWORD. '_index');
        });
    }

    /**
	 * A helper function that allows to drop an index if exists.
	 *
	 * @param Blueprint $table
	 * @param string    $indexName
	 *
	 * @throws DBALException
	 */
	private function dropIndexIfExists(Blueprint $table, string $indexName)
	{
		$doctrineTable = $this->schemaManager->introspectTable($table->getTable());
		if ($doctrineTable->hasIndex($indexName)) {
			$table->dropIndex($indexName);
		}
	}

	/**
	 * A helper function that allows to drop an unique constraint if exists.
	 *
	 * @param Blueprint $table
	 * @param string    $indexName
	 *
	 * @throws DBALException
	 */
	private function dropUniqueIfExists(Blueprint $table, string $indexName)
	{
		$doctrineTable = $this->schemaManager->introspectTable($table->getTable());
		if ($doctrineTable->hasUniqueConstraint($indexName)) {
			$table->dropUnique($indexName);
		}
	}
};
