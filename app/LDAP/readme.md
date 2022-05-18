
## LDAP interface for Lychee

### Installation

To use the the LDAP functionality you need to chose a development branch which support it (it should have the `LDAP_auth` branch merged in).

Follow the standard method to install lychee (see [Installation](https://lycheeorg.github.io/docs/installation.html)). Once lychee is running it 
can be configured via the settings dialog, which is available for the admin user.

### Configuration

To use LDAP as the login provider for lychee an LDAP provider needs be setup in the section LDAP in the settings.

### Settings

Setting the basic settings should be enough to enable the LDAP interface for Lychee. If needed advance options are available with the advanced settings.

#### Basic Settings 

| Setting           | Description                                                   | Type       | Default Value                 |
|-------------------|---------------------------------------------------------------|:----------:|-------------------------------|
| ldap_enabled      | LDAP login provider enabled                                   | 0|1        | 0                             |
| ldap_server       | LDAP server name                                              | string     |                               |
| ldap_port         | LDAP server port                                              | int        | 389                           |
| ldap_bind_dn      | LDAP bind dn                                                  | string     |                               |
| ldap_bind_pw      | LDAP bind password                                            | string     |                               |
| ldap_user_tree    | LDAP user tree                                                | string     |                               |
| ldap_user_filter  | LDAP user filter                                              | string     |                               |

#### Advanced Settings

| Setting           | Description                                                   | Type       | Default Value                 |
|-------------------|---------------------------------------------------------------|:----------:|-------------------------------|
| ldap_version      | LDAP protocol version                                         | int        | 3                             |
| ldap_user_key     | LDAP user key                                                 | string     | uid                           |
| ldap_user_scope   | LDAP user scope                                               | string     | sub                           |
| ldap_start_tls    | LDAP use STARTTLS protocol                                    | 0|1        | 0                             |
| ldap_referrals    | LDAP option referrals                                         | signed_int | -1                            |
| ldap_deref        | LDAP option deref                                             | 0|1        | 0                             |
| ldap_cn           | LDAP common name                                              | string     | cn                            |
| ldap_mail         | LDAP mail entry                                               | string     | mail                          |

#### Database Update Settings

| Setting           | Description                                                   | Type       | Default Value                 |
|-------------------|---------------------------------------------------------------|:----------:|-------------------------------|
| ldap_purge        | LDAP enables purging of obsolete users in lychee              | 0|1        | 0                             |
| ldap_update_users | LDAP schedule interval for automatic sync of users in minutes | int        | 0                             |

### Synchronizing Lychee with the LDAP Server

Lychee always relies on the LDAP server for the decission if a user can login to lychee or not. So only users which can be validated against the LADP server can login.

In addition users can share pictures and albums between them and therefore the list of users needs to be kept up to date in lychee.

The LDAP interface for Lychee support the synchonization with the following command:

`php artisan lychee:LDAP_update_all_users`

By default obsolete users are purged from the list of lychee users. If the users should be kept in the database even if the 
LDAP server do not know them any more, the following entry in the settings needs to be set to zero: `ldap_purge = 0`.

The synchronization can be automated, by configuring a cron-job to execute `/path-to-php/php artisan schedule:run 2>&1 >/dev/null` every minute. Based on this
 lychee runs its own scheduler to execute its jobs.

The frequency to run the snchonization between lyche and the LADP server can be controlled in the administration settings with 
the entry `ldap_update_users`. A typical value for the update frequency is 5 minutes. Then this value needs to be set to 5. The default value of zero
switches the automatic update off. If `ldap_enable = 1` the synchronisation can be performed by executing the `php artisan lychee:LDAP_update_all_users` command.
 
### Testing the LDAP Interface fpr Lychee

The LDAP interface for lychee can be tested using the public LDAP server from [Forum Systems](https://www.forumsys.com/2022/05/10/online-ldap-test-server/) 
with the follwing configuration:

| Setting           | Description                                                   | Value                                |
|-------------------|---------------------------------------------------------------|--------------------------------------|
| ldap_enabled      | LDAP login provider enabled                                   | 1                                    |
| ldap_server       | LDAP server name                                              | ldap.forumsys.com                    |
| ldap_port         | LDAP server port                                              | 389                                  |
| ldap_user_tree    | LDAP user tree                                                | dc=example,dc=com                    |
| ldap_user_filter  | LDAP user filter                                              | (uid=%{user})                        |
| ldap_bind_dn      | LDAP bind dn                                                  | cn=read-only-admin,dc=example,dc=com |
| ldap_bind_pw      | LDAP bind password                                            | password                             |

### Troubleshooting

In case of problems with the communication between lychee and the LDAP server the deubg logging should be activated in the `.env`-file:

| .env Entry        | Description                   | Default Value          | Recomended Value for Debugging       |
|-------------------|-------------------------------|------------------------|--------------------------------------|
| APP_LOG_LEVEL     | Application minimum log level | error                  | debug                                |

Then the communication between lynchee and the LDAP server can be found in the administrator menue (Show Logs).
