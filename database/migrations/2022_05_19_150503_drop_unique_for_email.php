<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUniqueForEmail extends Migration
{
	// The requirement for email adresses being unique has to be dropped in case
	// the users are managed externally e.g. by an ldap-server. The reason is an inherent
	// update problem.
	// Since the external server needs to take care of the uniqueness of the e-mail address,
	// nothing can be done in lychee in case if it is not unique.
	// If the uid of a user (his login name) gets changed, but his e-mail address does not then
	// the user cannot login any more if lychee uses LDAP. The reason is that lychee cannot update
	// the users table since the e-mail already exists and without a new entry in the users table
	// a login is not possible.
	// The only solution would be to develop a clever purge strategy that deletes non-existing users
	// first and call this strategy in case the users account cannot be created during login.
	// But if the external login provider delivers non unique email addresses, there will be no way out.
	// Since the uniqueness of the email address is not a data requirement, but just a measure to prevent
	// that people not get registered twice, I suggest to drop the unique requirement in the database.

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function (Blueprint $table) {
			$table->dropUnique(['email']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function (Blueprint $table) {
			$table->Unique(['email']);
		});
	}
}
