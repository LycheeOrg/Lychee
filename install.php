<?php
/**
 *  Simple straight forward installation script to run the following installtion steps via the web browser:
 *
 *  1. Installation of composer.phar
 *  2. copy .env.example to .env
 *  3.
 */
define('COMPOSER_INSTALLER','https://getcomposer.org/installer');
define('COMPOSER_HASH','55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae');
define('ENV','.env');
define('APP_KEY','APP_KEY=');

function getappkey() {
    $A=file(ENV);
    foreach($A as $a) {
        if (str_starts_with($a,APP_KEY)) {
            return trim(substr($a,strlen(APP_KEY)));
        }
    }
}

function print_o($A) {
    foreach ($A as $a) {
      echo $a."<br/>\n";
    }
    echo "<br/>\n";
    return true;
}

function H($a,$l=1) { 
    echo "<h$l>".$a."</h$l>";
    return true;
}

function T($a) { 
    echo $a."<br/>\n";
    return true;
}

function CL($a) { 
    echo "<li><code>".$a."</code></li>\n";
    return true;
}

function RUN($cmd,$get=false) { 
    $output='';
    $retval=null;
    $ret=exec($cmd,$output,$retval);
    if ($get) {
        return $ret;
    } else {
        return $output;
    }
}

chdir('..');
$OK=true;
if (!file_exists('.env')) {
    H('Copy .env.example to .env');
    copy('.env.example', '.env');
}

H('Installing Composer.phar');
copy(COMPOSER_INSTALLER, 'composer-setup.php');
if (hash_file('sha384', 'composer-setup.php') === COMPOSER_HASH) {
    T('Installer verified');
    exec('php composer-setup.php');
    unlink('composer-setup.php');
} else {
    T('Installer corrupt'); 
    unlink('composer-setup.php'); 
    $OK=false;
}

if ($OK) {
    H('Update composer.lock');
    print_o(RUN('php ./composer.phar update'));
}

if ($OK) {
    H('Install composer.json (no-dev)');
    print_o(RUN('php ./composer.phar install --no-dev'));
    H('Creating user.css',2);
    touch('public/dist/user.css');
    H('Creating the sqlite database',2);
    touch('database/database.sqlite');
}

if ($OK) {
    if (getappkey()=='') {
        H('Generate key');
        print_o(RUN('php artisan key:generate --force'));
    }
    H('Migrate database');
    print_o(RUN('php artisan migrate --force'));
}

if (getappkey()=='') {$OK=false;}

if ($OK) {
    T("<b>The installation is complete.</b>");
    T('<a href="/">Continue here.</a>');
    chdir('public');
    rename("install.php","../install.php");
} else {
    T("Installation error. Run thr following steps manually in the installtion directory:");
    T('<ol dir="auto">');
    CL('php -r "'."copy('https://getcomposer.org/installer', 'composer-setup.php'".');\"');
    CL('php -r "'."if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;".'"');
    CL('php composer-setup.php');
    CL('php -r "'."unlink('composer-setup.php');".'"');
    CL("cp .env.example .env");
    CL("./composer.phar update");
    CL("./composer.phar install --no-dev --prefer-dist");
    CL("php artisan key:generate");
    CL("php artisan migrate");
    T("<li><code>mv public/install.php .</code></li>");
    T('</ol>');
}
