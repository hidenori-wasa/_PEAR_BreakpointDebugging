<?php

/**
 * Class for breakpoint debugging in case of debug mode.
 *
 * LICENSE:
 * Copyright (c) 2012-, Hidenori Wasa
 * All rights reserved.
 *
 * License content is written in "PEAR/BreakpointDebugging/BREAKPOINTDEBUGGING_LICENSE.txt".
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://opensource.org/licenses/mit-license.php  MIT License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
// File to have "use" keyword does not inherit scope into a file including itself,
// also it does not inherit scope into a file including,
// and moreover "use" keyword alias has priority over class definition,
// therefore "use" keyword alias does not be affected by other files.
use \BreakpointDebugging as B;
use \BreakpointDebugging_InAllCase as BA;
use \BreakpointDebugging_Window as BW;

/**
 * This class executes error or exception handling, and it is except release mode.
 *
 * PHP version 5.3.2-5.4.x
 *
 * "*_InDebug.php" file does not use on release. Therefore, response time is zero in release.
 * These file names put "_" to cause error when we do autoload.
 *
 * ### The basic concept. ###
 * First, I thank it that I could learn concept of debugging with "WRITING SOLID CODE" (Author: Steve Maguire : Microsoft Press, 1995).
 *
 * This is package for breakpoint debugging.
 * Also, you can use as basics of other PHP program or PEAR package if you want
 * because this package has necessary minimum features.
 *      It is "php.ini" file setting fixation feature, PHP code synchronization feature,
 *      error log feature by global handler,
 *      the override class feature and execution mode.
 *
 * ### Environment which can do step execution. ###
 * Recommendation IDE.
 *      "NetBeans IDE 8.0".
 *      Because HTML3, CSS3, JavaScript(JQuery) and PHP is useful for input helper and step execution.
 *      NOTICE: We must code "if" statement of plural line as below because its line feed cannot delete by format.
 *          if ($a              // Description 1.
 *              || $b           // Description 2.
 *              || ($c || $d)   // Description 3.
 *              || ($e && $f)   // Description 4.
 *              && $g           // Description 5.
 *              && ($h || $i)   // Description 6.
 *              && ($j && $k)   // Description 7.
 *          ) {
 *
 * Recommendation environment for "PHP5.3" Windows.
 *      "WindowsXP Professional (VC6)" + "XAMPP 1.7.4 (Last of 'VC6'.)" + "php_xdebug-2.1.2-5.3-vc6.dll (Last of 'VC6'.)".
 *      However, other OS is possible by using "XAMPP" and "XDebug" of "VC9" or "VC11".
 * Recommendation environment for "PHP5.4" Windows.
 *      I am unknown about this.
 *      However, "WindowsXP Professional (VC6)" is impossible for step execution.
 * Recommendation environment for "PHP5.5" Windows.
 *      I am unknown about this.
 *      However, "WindowsXP Professional (VC6)" is impossible for step execution.
 *
 * Recommendation setting procedure for development of "XAMPP 1.7.4".
 *      ### "XAMPP" setting procedure. ###
 *      // We must disconnect inbound connection except "BOOTPC" and "DOMAIN" of "C:\WINDOWS\system32\svchost.exe" by firewall of a software.
 *      // Disable "IIS" server because port 80 conflicts with "Apache".
 *      // Create "lang.tmp" file. ( In case of Japan )
 *      C:\xampp\htdocs\xampp\lang.tmp
 *          ja
 *      // Disconnect outbound HTTP connection of Apache.
 *      C:\xampp\apache\conf\httpd.conf
 *          before:
 *          Listen 80
 *          after:
 *          Listen 127.0.0.1:80
 *      // Disconnect outbound SSL connection of Apache.
 *      C:\xampp\apache\conf\extra\httpd-ssl.conf
 *          before:
 *          Listen 443
 *          after:
 *          Listen 127.0.0.1:443
 *      // Change the configuration file of "MySQLi".
 *      C:\xampp\mysql\bin\my.ini
 *              .
 *              .
 *              .
 *          [mysqld]
 *          default-storage-engine=innodb
 *          character-set-server=utf8
 *          collation-server=utf8_general_ci
 *          # Ignores character sets information which was sent from client, so it uses character sets of default of server.
 *          skip-character-set-client-handshake
 *          # Database compression for performance. CAUTION: We must not use plural compression format in database.
 *          #innodb_file_format=Barracuda
 *          # Makes a file per table for performance. CAUTION: We must not use plural table format in database.
 *          #innodb_file_per_table=1
 *              .
 *              .
 *              .
 *          [mysqldump]
 *          default-character-set=utf8
 *              .
 *              .
 *              .
 *          [mysql]
 *          default-character-set=utf8
 *              .
 *              .
 *              .
 *      // Change setting of "phpMyAdmin".
 *          // Execute "C:\xampp\phpMyAdmin\scripts\create_tables.sql" import by "phpMyAdmin".
 *          // Then, change "C:\xampp\phpMyAdmin\config.inc.php" file.
 *              before:
 *              $cfg['Servers'][$i]['auth_type']     = 'http';      // Authentication method (config, http or cookie based)
 *              $cfg['Servers'][$i]['password'] = '';
 *              $cfg['Servers'][$i]['AllowNoPassword'] = true;
 *              after:
 *              $cfg['Servers'][$i]['auth_type']     = 'config';      // Authentication method (config, http or cookie based)
 *              $cfg['Servers'][$i]['password'] = '<your password>';
 *              // $cfg['Servers'][$i]['AllowNoPassword'] = true;
 *              $cfg['Servers'][$i]['tracking'] = 'pma_tracking';
 *              $cfg['Servers'][$i]['userconfig'] = 'pma_userconfig';
 *      // Extensions which "phpMyAdmin" needs. ( Confirms by "phpinfo()" )
 *          "zlib" or "bz2"
 *          "mbstring"
 *          "ctype"
 *          "GD2"
 *          "mcrypt"
 *
 *      ### "XDebug" setting procedure. ###
 *      // Place "php_xdebug-2.1.2-5.3-vc6.dll" file ( last versions for this OS ) to "C:\xampp\php\ext\".
 *      // Set "C:\xampp\php\php.ini" file as follows.
 *      zend_extension = "C:\xampp\php\ext\php_xdebug-2.1.2-5.3-vc6.dll"
 *      // Then, use this package.
 *
 * Recommendation environment for "PHP5.3" Linux.
 *      "Ubuntu12.04LTS desktop" + "This Ubuntu's LAMP" + "This Ubuntu's XDebug".
 *      Because "apt-get" command controls version of "LAMP", "phpMyAdmin" and "XDebug" for OS.
 *      Therefore, we can do step execution.
 * Recommendation environment for "PHP5.4" Linux.
 *      "Ubuntu13.10 desktop" + "This Ubuntu's LAMP" + "This Ubuntu's XDebug".
 * Recommendation environment for "PHP5.5" Linux.
 *      "Ubuntu14.04LTS desktop" + "This Ubuntu's LAMP" + "This Ubuntu's XDebug".
 *
 * Recommendation setting procedure for development of "Ubuntu12.04LTS desktop".
 *      ### Uninstalling procedure. ###
 *      // Uninstalls "PEAR".
 *      sudo apt-get purge php-pear
 *      // Uninstalls "phpMyAdmin".
 *      sudo apt-get purge phpmyadmin
 *      // Uninstalls "LAMP".
 *      sudo tasksel remove lamp-server
 *
 *      ### installing procedure. ###
 *      // Installs "LAMP".
 *      sudo apt-get update
 *      sudo apt-get install tasksel
 *      sudo tasksel install lamp-server
 *      // Installs "phpMyAdmin".
 *      sudo apt-get install phpmyadmin
 *      sudo firefox "/usr/share/doc/phpmyadmin/Documentation.html"
 *      // Installs "PEAR".
 *      sudo apt-get install php-pear
 *      // Deletes unnecessary package.
 *      sudo apt-get autoremove
 *
 *      ### "LAMPP" setting procedure. ###
 *      // Disconnect inbound connection by using "ufw".
 *          // Enables firewall.
 *          sudo ufw enable
 *          // Disconnects inbound connection.
 *          sudo ufw default DENY
 *          // Disables logging.
 *          sudo ufw logging off
 *          // Confirms status of firewall.
 *          sudo ufw status verbose
 *      // Disconnects outbound connection of Apache and enables SSL of Apache.
 *      gksudo gedit /etc/apache2/ports.conf
 *          // before:
 *          Listen 80
 *          // after:
 *          Listen 127.0.0.1:80
 *          // before:
 *          Listen 443
 *          // after:
 *          NameVirtualHost *:443
 *          Listen 127.0.0.1:443
 *      // Sets user and group of Apache, and sets symbolic link to "phpMyAdmin".
 *      gksudo gedit /etc/apache2/httpd.conf
 *          User <your user name>
 *          Group <your group name>
 *
 *          Alias /phpmyadmin "/usr/share/phpmyadmin"
 *          <Directory "/usr/share/phpmyadmin/">
 *              Options MultiViews FollowSymLinks
 *              AllowOverride None
 *              Order deny,allow
 *              Deny from all
 *              Allow from 127.0.0.1
 *          </Directory>
 *      // Set "MySQLi".
 *      gksudo gedit /etc/mysql/my.cnf
 *              .
 *              .
 *              .
 *          [mysqld]
 *          default-storage-engine=innodb
 *          character-set-server=utf8
 *          collation-server=utf8_general_ci
 *          # Ignores character sets information which was sent from client, so it uses character sets of default of server.
 *          skip-character-set-client-handshake
 *          # Database compression for performance. CAUTION: We must not use plural compression format in database.
 *          #innodb_file_format=Barracuda
 *          # Makes a file per table for performance. CAUTION: We must not use plural table format in database.
 *          #innodb_file_per_table=1
 *              .
 *              .
 *              .
 *          [mysqldump]
 *          default-character-set=utf8
 *              .
 *              .
 *              .
 *          [mysql]
 *          default-character-set=utf8
 *              .
 *              .
 *              .
 *      // Change setting of "phpMyAdmin".
 *          // Executes "/usr/share/doc/phpmyadmin/examples/create_tables_sql.gz" import by "phpMyAdmin".
 *          // Copies the sample configuration file to configuration file.
 *          sudo cp /usr/share/phpmyadmin/config.sample.inc.php /etc/phpmyadmin/config.inc.php
 *          // Edits the configuration file.
 *          sudo gedit /etc/phpmyadmin/config.inc.php
 *              before:
 *              $cfg['Servers'][$i]['auth_type'] = 'cookie';
 *              after:
 *              $cfg['Servers'][$i]['auth_type']     = 'config';
 *              $cfg['Servers'][$i]['user']          = 'root';
 *              $cfg['Servers'][$i]['password']      = 'wasapass'; // use here your password
 *              before:
 *              // $cfg['Servers'][$i]['pmadb'] = 'phpmyadmin';
 *              // $cfg['Servers'][$i]['bookmarktable'] = 'pma_bookmark';
 *              // $cfg['Servers'][$i]['relation'] = 'pma_relation';
 *              // $cfg['Servers'][$i]['table_info'] = 'pma_table_info';
 *              // $cfg['Servers'][$i]['table_coords'] = 'pma_table_coords';
 *              // $cfg['Servers'][$i]['pdf_pages'] = 'pma_pdf_pages';
 *              // $cfg['Servers'][$i]['column_info'] = 'pma_column_info';
 *              // $cfg['Servers'][$i]['history'] = 'pma_history';
 *              // $cfg['Servers'][$i]['tracking'] = 'pma_tracking';
 *              // $cfg['Servers'][$i]['designer_coords'] = 'pma_designer_coords';
 *              // $cfg['Servers'][$i]['userconfig'] = 'pma_userconfig';
 *              after:
 *              $cfg['Servers'][$i]['pmadb'] = 'phpmyadmin';
 *              $cfg['Servers'][$i]['bookmarktable'] = 'pma_bookmark';
 *              $cfg['Servers'][$i]['relation'] = 'pma_relation';
 *              $cfg['Servers'][$i]['table_info'] = 'pma_table_info';
 *              $cfg['Servers'][$i]['table_coords'] = 'pma_table_coords';
 *              $cfg['Servers'][$i]['pdf_pages'] = 'pma_pdf_pages';
 *              $cfg['Servers'][$i]['column_info'] = 'pma_column_info';
 *              $cfg['Servers'][$i]['history'] = 'pma_history';
 *              $cfg['Servers'][$i]['tracking'] = 'pma_tracking';
 *              $cfg['Servers'][$i]['designer_coords'] = 'pma_designer_coords';
 *              $cfg['Servers'][$i]['userconfig'] = 'pma_userconfig';
 *      // Extensions which "phpMyAdmin" needs. ( Confirms by "phpinfo()" )
 *          "zlib" or "bz2"
 *          "mbstring"
 *          "ctype"
 *          "GD2"
 *          "mcrypt"
 *      // Creates a document root.
 *      mkdir ~/private-www
 *      // Copies the default virtual host setting file to new file "mysite".
 *      sudo cp /etc/apache2/sites-available/default /etc/apache2/sites-available/mysite
 *      // Copies the default SSL virtual host setting file to new file "mysite-ssl".
 *      sudo cp /etc/apache2/sites-available/default-ssl /etc/apache2/sites-available/mysite-ssl
 *      // Edits the "mysite" file.
 *      gksudo gedit /etc/apache2/sites-available/mysite
 *          // before:
 *          DocumentRoot /var/www
 *          // after:
 *          DocumentRoot /home/<your user name>/private-www
 *          // before:
 *          <Directory /var/www/>
 *          // after:
 *          <Directory /home/<your user name>/private-www/>
 *      // Edits the "mysite-ssl" file.
 *      gksudo gedit /etc/apache2/sites-available/mysite-ssl
 *          // before:
 *          <VirtualHost _default_:443>
 *          // after:
 *          <VirtualHost *:443>
 *          // before:
 *          DocumentRoot /var/www
 *          // after:
 *          DocumentRoot /home/<your user name>/private-www
 *          // before:
 *          <Directory /var/www/>
 *          // after:
 *          <Directory /home/<your user name>/private-www/>
 *      // Disables default virtual host and enables new virtual host.
 *      sudo a2dissite default && sudo a2ensite mysite
 *      // Disables default SSL virtual host and enables new SSL virtual host.
 *      sudo a2dissite default-ssl && sudo a2ensite mysite-ssl
 *      // // Readjusts the root user password of "MySQLi". ( If necessary )
 *      // mysql -u root -p
 *      // mysql>SET PASSWORD FOR 'root'@'localhost' = PASSWORD('<your password>');
 *
 *      ### "XDebug" setting procedure. ###
 *      // Installs "XDebug" package.
 *      sudo apt-get install php5-xdebug
 *      // Displays the file path of "xdebug.so".
 *      sudo find "/usr/lib/php5" -name "xdebug.so"
 *      // Adds setting of "XDebug" package to "php.ini" file.
 *      gksudo gedit /etc/php5/apache2/php.ini
 *          [xdebug]
 *          zend_extension = "/usr/lib/php5/<DATE+lfs>/xdebug.so"
 *          xdebug.remote_enable = 1
 *          xdebug.remote_handler = dbgp
 *          xdebug.remote_mode = req
 *          xdebug.remote_host = 127.0.0.1
 *          xdebug.remote_port = 9000
 *      // Confirms by CLI.
 *      php -v
 *      // Confirms by CGI.
 *      gedit ~/private-www/index.php
 *          <?php phpinfo(); ?>
 *      firefox "localhost"
 *      // Then, use this package.
 *
 *      ### Execution file creation. ###
 *      // Creates Apache execution file.
 *      gedit ~/<desktop>/Apache.sh
 *          #!/bin/bash
 *          # Restarts Apache.
 *          sudo service apache2 restart
 *          # Stands by at this line until input.
 *          read Wait
 *          # Stops Apache.
 *          sudo service apache2 stop
 *      // Creates "MySQLi" execution file.
 *      gedit ~/<desktop>/MySQLi.sh
 *          #!/bin/bash
 *          # Restarts Apache.
 *          sudo service apache2 restart
 *          # Restarts "MySQLi".
 *          sudo service mysql restart
 *          # Stands by at this line until input.
 *          read Wait
 *          # Stops "MySQLi".
 *          sudo service mysql stop
 *          # Stops Apache.
 *          sudo service apache2 stop
 *      // Creates "phpMyAdmin" execution file.
 *      gedit ~/<desktop>/phpMyAdmin.sh
 *          #!/bin/bash
 *          # Restarts Apache.
 *          sudo service apache2 restart
 *          # Restarts "MySQLi".
 *          sudo service mysql restart
 *          # Starts "phpMyAdmin".
 *          sudo firefox "localhost/phpmyadmin/"
 *          # Stands by at this line until input.
 *          read Wait
 *          # Stops "MySQLi".
 *          sudo service mysql stop
 *          # Stops Apache.
 *          sudo service apache2 stop
 *      // Makes created shell files executable.
 *      find ~/<desktop>/ -type f -regex ".+\.sh" -exec sudo chmod 0700 {} \;
 *
 * ### Recommendation file cache extention of production server. ###
 * I recommend "Zend OPcache" extention.
 * Because this extension is stable.
 *      1. My unit tests succeeded with it. Such as calling CLI from CGI with "popen()" function.
 *      2. It is core extension of "PHP5.5".
 *      3. Its development team have several "PHP" coder.
 * Also, this extension is fast.
 *      1. The speed decelerates hardly even if the number of users increases.
 *      2. This extention caches op code after optimization.
 *              As the example, we can code to except debugging code from cache at release.
 *              Example:
 *                  if (false) { // Excepts from this line.
 *                      // Debug codes.
 *                          .
 *                          .
 *                          .
 *                  } // Excepts until this line.
 * How to make this Zend extention is same as pecl extention in case of Windows "VC9".
 * Usage of "php_opcache.dll" file:
 *      1. Move to "C:\xampp\php\ext\php_opcache.dll".
 *      2. Add following lines into "php.ini" file, then save the file.
 *          zend_extension = "C:\xampp\php\ext\php_opcache.dll" ; This line must be before next line.
 *          zend_extension = "...\php_xdebug-....dll"
 *
 *          [Zend OPcache]
 *          opcache.memory_consumption = 128
 *          opcache.interned_strings_buffer = 8
 *          opcache.max_accelerated_files = 4000
 *          opcache.fast_shutdown = 1
 *          ; Constant Value: 0         We cannot call command to get result from CGI because a deprecated prefix is added in case of first time.
 *          opcache.enable_cli = 0
 *          ; Constant Value: 1         Because we must cache modified "*.php" files.
 *          opcache.validate_timestamps = 1
 *          ; Development Value: 0      Because we must cache modified "*.php" files.
 *          ; Production Value: 2       Because production server want to modify a file during execution.
 *          opcache.file_update_protection = 0
 *          ; Development Value: 0      Because we must cache modified "*.php" files.
 *          ; Production Value: 60      Because production server does not want to access a file as much as possible.
 *          opcache.revalidate_freq = 0
 *      3. Restart apache.
 *
 * ### The advantage of breakpoint debugging. ###
 * Can find a position of a bug immediately.
 * In addition to it, we can examine its result by selecting and pointing
 * (variable, function or conditional expression) of code using mouse pointer
 * except variable of "use as" statement,
 * if we check "the watch and the balloon evaluation" of "[tool] - [option] - [PHP] - [debug]" in case of "NetBeans IDE".
 * Also, we have to empty watch variables when its variable does not exist.
 * Therefore, can debug quickly.
 *
 * ### How to code breakpoint debugging. ###
 * We must code as follows to process in "BreakpointDebugging" class.
 * We should verify an impossible "parameters and return value" of
 * "function and method" with "\BreakpointDebugging::assert()".
 * Also, we should verify other impossible values of those.
 * We do not need error and exception handler coding because an error and an exception
 * which wasn't caught are processed by global handler in "BreakpointDebugging" class.
 *
 * Example:
 *      <?php
 *
 *      require_once './BreakpointDebugging_Inclusion.php';
 *
 *      use \BreakpointDebugging as B;
 *
 *      B::checkExeMode(); // Checks the execution mode.
 *
 *      $exeMode = B::getStatic('$exeMode');
 *      $logData = 'Data character string.';
 *      if ($exeMode & B::RELEASE) { // If release execution mode.
 *          $lockByFlock = &\BreakpointDebugging_LockByFlock::singleton(); // Creates a lock instance.
 *          $lockByFlock->lock(); // Locks php-code.
 *          B::filePutContents('Somethig.log', $logData);
 *          $lockByFlock->unlock(); // Unlocks php-code.
 *      } else { // If debug execution mode.
 *          B::assert(is_string($logData));
 *          echo $logData;
 *      }
 *
 *      ?>
 *
 * ### Running procedure. ###
 * Please, run the following procedure.
 * Procedure 1: Install "XDebug" by seeing "http://xdebug.org/docs/install"
 *      in case of your local host.
 *      "Xdebug" extension is required because "uses breakpoint,
 *      displays for fatal error and detects infinity recursive function call".
 * Procedure 2: If you want remote debug, set 'xdebug.remote_host =
 *      "<name or ip of your host which debugger exists>"' into "php.ini" file, if remote server supports.
 * Procedure 3: Set *.php file format to utf8, but we should create backup of
 *      php files because multibyte strings may be destroyed.
 * Procedure 4: Copy
 *          "BreakpointDebugging_Inclusion.php"
 *      into your project directory.
 *      And, copy
 *          "BreakpointDebugging_MySetting*.php"
 *      to "const BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME" directory of your project directory.
 * Procedure 5: Edit BreakpointDebugging_MySetting*.php for customize.
 *      Then, it fixes part setting about all execution modes.
 *      Especially, setting to "const BREAKPOINTDEBUGGING_IS_PRODUCTION = true;" by "./BreakpointDebugging_ProductionSwitcher.php" is important to security in case of production server.
 * Procedure 6: Implement a code like "Example" of "How to code breakpoint debugging" section.
 * Procedure 7: Choose the following execution mode into "BreakpointDebugging_MySetting.php".
 *          define('BREAKPOINTDEBUGGING_MODE', 'DEBUG_UNIT_TEST');          // (Development mode. Requires "BreakpointDebugging_PHPUnit" package.)
 *          // define('BREAKPOINTDEBUGGING_MODE', 'RELEASE_UNIT_TEST');     // (Development mode. Requires "BreakpointDebugging_PHPUnit" package.)
 *          // define('BREAKPOINTDEBUGGING_MODE', 'DEBUG');                 // (Development mode.)
 *          // define('BREAKPOINTDEBUGGING_MODE', 'RELEASE');               // (Development mode.)
 *      Then, we can use "B::getStatic('$exeMode')" to get execution mode.
 *      Also, see the file level document of "BreakpointDebugging_PHPUnit.php" file about unit test.
 *
 *      Please, follow execution mode procedure.
 *          Procedure 1: Use "DEBUG" or "DEBUG_UNIT_TEST" mode at local server. This mode can do debug step execution.
 *          Procedure 2: Use "RELEASE" or "RELEASE_UNIT_TEST" mode at local server. This mode can do release step execution.
 *          Procedure 3: Use "DEBUG" or "DEBUG_UNIT_TEST" mode at remote server. This mode can do production server display debugging.
 *          Procedure 4: Use "RELEASE" or "RELEASE_UNIT_TEST" mode at remote server. This mode can do production server logging debugging.
 *      If you changed remote "php.ini" file, you must redo from procedure 3 because to increase the production mode execution speed.
 * Procedure 8: Release the code to production server. And, change it to production mode.
 *      See the file level document of "BreakpointDebugging_ProductionSwitcher.php" file.
 *
 * Caution: Do not execute "ini_set('error_log', ...)" because this package uses local log rotation instead of system log.
 *
 * Option procedure: Register at top of the function or method or file
 *      which has been not fixed. Copy following.
 *      "\BreakpointDebugging::registerNotFixedLocation(self::$_isRegister[__METHOD__]);"
 *      Then, we can discern function or method or file
 *      which has been not fixed with browser screen or log.
 * Option procedure: Register local variable or global variable
 *      which you want to see with "\BreakpointDebugging::addValuesToTrace()".
 *
 * ### Exception hierarchical structure ###
 *  PEAR_Exception
 *      BreakpointDebugging_Exception_InAllCase
 *          BreakpointDebugging_Exception
 *              BreakpointDebugging_ErrorException
 *              BreakpointDebugging_OutOfLogRangeException
 *
 * ### Useful class index. ###
 * This class override a class without inheritance, but only public member can be inherited.
 *      class BreakpointDebugging_OverrideClass
 * Class which locks php-code by file existing.
 *      class BreakpointDebugging_LockByFileExisting
 * Class which locks php-code by shared memory operation.
 *      class BreakpointDebugging_LockByShmopRequest
 * Class which locks php-code by "flock()".
 *      class BreakpointDebugging_LockByFlock
 *
 * My viewpoint about PHP-types for reading my PHP code.
 *      ### About PHP-types structure.
 *      Any type of PHP has ID which has pointer which specifies movable memory of "type and value".
 *      Its movable memory has "reference count" and "flag which means a reference".
 *
 *      ### About variable copy.
 *      Variable copy is ID copy, and it increments reference count of movable memory.
 *      And, object type copy is ID copy too. However, object type is not scalar type.
 *      Therefore, we must use "$cloneObject = clone $object;" if we want object internal copy.
 *      And, array type copy is its elements ID copy. However, element of reference specifies same ID area.
 *      Therefore, copied element value may be changed if original copy element is changed even though we copied array type.
 *
 *      ### About variable reference copy.
 *      Variable reference copy specifies same ID area, and it increments reference count of movable memory, and checks "flag which means a reference".
 *
 *      ### About "unset()" function.
 *      "unset()" function decrements reference count.
 *      Then, memory area which is pointed is deleted if reference count became 0.
 *
 *      ### About "__destruct()" class method call.
 *      "__destruct()" class method is called if we overwrite null value to variable because value of all reference is disabled.
 *      However, memory area and reference count is kept.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://opensource.org/licenses/mit-license.php  MIT License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
final class BreakpointDebugging extends \BreakpointDebugging_InAllCase
{
    /**
     * The class method call locations.
     *
     * @var array
     */
    private static $_callLocations = array ();

    /**
     * Setting option filenames.
     *
     * @var array
     */
    private static $_onceFlagPerPackageInDebug = array ();

    /**
     * Include-paths.
     *
     * @var string
     */
    private static $_includePaths;

    /**
     * Limits static properties accessing.
     *
     * @return void
     */
    static function initialize()
    {
        B::limitAccess('BreakpointDebugging.php');

        B::assert(func_num_args() === 0);

        parent::initialize();

        parent::$staticProperties['$_includePaths'] = &self::$_includePaths;
        parent::$staticPropertyLimitings['$exeMode'] = 'BreakpointDebugging_PHPUnit.php';
        $tmp = BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php';
        parent::$staticPropertyLimitings['$_maxLogFileByteSize'] = $tmp;
        parent::$staticPropertyLimitings['$_maxLogParamNestingLevel'] = $tmp;
        parent::$staticPropertyLimitings['$_maxLogElementNumber'] = $tmp;
        parent::$staticPropertyLimitings['$_maxLogStringSize'] = $tmp;
        parent::$staticPropertyLimitings['$_workDir'] = $tmp;
        parent::$staticPropertyLimitings['$_developerIP'] = $tmp;
        parent::$staticPropertyLimitings['$_onceErrorDispFlag'] = 'BreakpointDebugging/PHPUnit/FrameworkTestCase.php';
        parent::$staticPropertyLimitings['$_callingExceptionHandlerDirectly'] = array ('BreakpointDebugging/ErrorInAllCase.php',);
    }

    /**
     * If "Apache HTTP Server" does not support "suEXEC", this method displays security warning.
     *
     * @return void
     */
    static function checkSuperUserExecution()
    {
        if (BREAKPOINTDEBUGGING_IS_WINDOWS) { // In case of Windows.
            return;
        }
        $processUser = posix_getpwuid(posix_geteuid());
        // If this is remote debug, unix and root user.
        if (BA::$exeMode === B::REMOTE //
            && $processUser['name'] === 'root' //
        ) {
            BW::virtualOpen(parent::ERROR_WINDOW_NAME, parent::getErrorHtmlFileTemplate());
            BW::htmlAddition(B::ERROR_WINDOW_NAME, 'pre', 0, 'Security warning: Recommends to change to "Apache HTTP Server" which Supported "suEXEC" because this "Apache HTTP Server" is executed by "root" user.');
        }
    }

    /**
     * For debug.
     *
     * @param string $propertyName Same as parent.
     *
     * @return Same as parent.
     */
    static function getStatic($propertyName)
    {
        self::assert(func_num_args() === 1);
        self::assert(is_string($propertyName));

        return parent::getStatic($propertyName);
    }

    /**
     * Gets a static property reference.
     *
     * @param string $propertyName Static property name.
     *
     * @return mixed& Static property.
     */
    static function &refStatic($propertyName)
    {
        self::limitAccess(parent::$staticPropertyLimitings[$propertyName]);

        self::assert(func_num_args() === 1);
        self::assert(is_string($propertyName));

        return parent::refStatic($propertyName);
    }

    /**
     * For debug.
     *
     * @return Same as parent.
     */
    static function getXebugExists()
    {
        self::assert(func_num_args() === 0);

        return parent::getXebugExists();
    }

    /**
     * For debug.
     *
     * @param bool $value Same as parent.
     *
     * @return Same as parent.
     */
    static function setXebugExists($value)
    {
        self::limitAccess('BreakpointDebugging.php');

        self::assert(func_num_args() === 1);
        self::assert(is_bool($value));

        parent::setXebugExists($value);
    }

    /**
     * For debug.
     *
     * @param string $phpIniVariable Same as parent.
     * @param mixed  $cmpValue       Same as parent.
     * @param string $errorMessage   Same as parent.
     *
     * @return Same as parent.
     */
    static function iniCheck($phpIniVariable, $cmpValue, $errorMessage)
    {
        self::assert(func_num_args() === 3);
        self::assert(is_string($phpIniVariable));
        self::assert(is_string($cmpValue) || is_array($cmpValue));
        self::assert(is_string($errorMessage));

        parent::iniCheck($phpIniVariable, $cmpValue, $errorMessage);
    }

    /**
     * For debug.
     *
     * @param string $string Same as parent.
     *
     * @return Same as parent.
     */
    static function convertMbString($string)
    {
        self::assert(func_num_args() === 1);
        self::assert(is_string($string));

        return parent::convertMbString($string);
    }

    /**
     * For debug.
     *
     * @param string $name              Same as parent.
     * @param int    $permission        Same as parent.
     * @param int    $timeout           Same as parent.
     * @param int    $sleepMicroSeconds Same as parent.
     *
     * @return Same as parent.
     */
    static function chmod($name, $permission, $timeout = 10, $sleepMicroSeconds = 1000000)
    {
        self::assert(func_num_args() <= 4);
        self::assert(is_string($name));
        self::assert(is_int($permission));
        self::assert(is_int($timeout));
        self::assert(is_int($sleepMicroSeconds));

        return parent::chmod($name, $permission, $timeout, $sleepMicroSeconds);
    }

    /**
     * For debug.
     *
     * @param array $params            Same as parent.
     * @param int   $timeout           Same as parent.
     * @param int   $sleepMicroSeconds Same as parent.
     *
     * @return Same as parent.
     */
    static function mkdir(array $params, $timeout = 10, $sleepMicroSeconds = 1000000)
    {
        self::assert(func_num_args() <= 3);
        self::assert(is_int($timeout));
        self::assert(is_int($sleepMicroSeconds));

        return parent::mkdir($params, $timeout, $sleepMicroSeconds);
    }

    /**
     * For debug.
     *
     * <pre>
     * Example:
     *
     * <code>
     *      $pFile = B::fopen(array ($filePath, 'w+b'));
     * </code>
     *
     * </pre>
     *
     * @param array $params            Same as parent.
     * @param int   $permission        Same as parent.
     * @param int   $timeout           Same as parent.
     * @param int   $sleepMicroSeconds Same as parent.
     *
     * @return Same as parent.
     */
    static function fopen(array $params, $permission = 0600, $timeout = 10, $sleepMicroSeconds = 1000000)
    {
        self::assert(func_num_args() <= 4);
        self::assert(is_int($permission) && 0 <= $permission && $permission <= 0777);
        self::assert(is_int($timeout));
        self::assert(is_int($sleepMicroSeconds));

        return parent::fopen($params, $permission, $timeout, $sleepMicroSeconds);
    }

    /**
     * For debug.
     *
     * @param array $intArray Same as parent.
     *
     * @return Same as parent.
     */
    static function compressIntArray($intArray)
    {
        self::assert(func_num_args() === 1);
        self::assert(is_array($intArray));

        return parent::compressIntArray($intArray);
    }

    /**
     * For debug.
     *
     * @param mixed $compressBytes Same as parent.
     *
     * @return Same as parent.
     */
    static function decompressIntArray($compressBytes)
    {
        self::assert(func_num_args() === 1);
        self::assert(is_string($compressBytes) || $compressBytes === false);

        return parent::decompressIntArray($compressBytes);
    }

    /**
     * For debug.
     *
     * @param object $pException Same as parent.
     *
     * @return Same as parent.
     */
    static function handleException($pException)
    {
        self::assert(func_num_args() === 1);
        self::assert($pException instanceof \Exception);

        if (BA::$exeMode & B::UNIT_TEST) {
            \BreakpointDebugging_PHPUnit::handleUnitTestException($pException);
        }

        parent::handleException($pException);
    }

    /**
     * For debug.
     *
     * @param int    $errorNumber  Same as parent.
     * @param string $errorMessage Same as parent.
     *
     * @return Same as parent.
     */
    static function handleError($errorNumber, $errorMessage)
    {
        self::assert(is_int($errorNumber));
        self::assert(is_string($errorMessage));

        return parent::handleError($errorNumber, $errorMessage);
    }

    /**
     * Checks path environment variable for "php" command.
     *
     * @return void
     */
    static function checkPathEnvironmentVariable()
    {
        if (BREAKPOINTDEBUGGING_IS_WINDOWS) {
            $paths = explode(';', getenv('path'));
            while (true) {
                foreach ($paths as $path) {
                    $path = rtrim($path, '\/');
                    if (is_file($path . '/php.exe')) {
                        break 2;
                    }
                }
                BW::virtualOpen(parent::ERROR_WINDOW_NAME, parent::getErrorHtmlFileTemplate());
                BW::htmlAddition(B::ERROR_WINDOW_NAME, 'pre', 0, 'Path environment variable has not been set for "php.exe" command.' . PHP_EOL . `path`);
                exit;
            }
        }
    }

    ///////////////////////////// For package user from here in case of debug mode. /////////////////////////////
    /**
     * Checks a invoker file path.
     *
     * @param array  $includePaths    The including paths.
     * @param string $invokerFilePath Invoker file path.
     * @param string $fullFilePath    A full file path.
     *
     * @return boolean
     */
    private static function _checkInvokerFilePath($includePaths, $invokerFilePath, $fullFilePath)
    {
        B::assert(func_num_args() === 3);
        B::assert(is_array($includePaths));
        B::assert(is_string($invokerFilePath));
        B::assert(is_string($fullFilePath));

        foreach ($includePaths as $includePath) {
            $invokerFullFilePath = realpath("$includePath/$invokerFilePath");
            if ($invokerFullFilePath === false) {
                continue;
            }
            if ($fullFilePath === $invokerFullFilePath) {
                return true;
            }
        }
        return false;
    }

    /**
     * Limits the invoker file paths.
     *
     * @param mixed $invokerFilePaths Invoker file paths.
     * @param bool  $enableUnitTest   Is this enable in unit test?
     *
     * @return void
     */
    static function limitAccess($invokerFilePaths, $enableUnitTest = false)
    {
        $callStack = debug_backtrace();
        // Makes invoking location information.
        $count = count($callStack);
        if ($count === 1) {
            // @codeCoverageIgnoreStart
            // Because unit test file is not top page.
            // Skips top page.
            return;
            // @codeCoverageIgnoreEnd
        }
        do {
            for ($key = 1; $key < $count; $key++) {
                if (array_key_exists('file', $callStack[$key])) {
                    break 2;
                }
                // @codeCoverageIgnoreStart
                // Because unit test cannot run "call_user_func_array()" as global code.
            }
            // Skips when "file" key does not exist.
            return;
            // @codeCoverageIgnoreEnd
        } while (false);
        $fullFilePath = $callStack[$key]['file'];
        $line = $callStack[$key]['line'];
        if (array_key_exists($fullFilePath, self::$_callLocations) //
            && array_key_exists($line, self::$_callLocations[$fullFilePath]) //
        ) {
            // Skips same.
            return;
        }
        // Stores the invoking location information.
        self::$_callLocations[$fullFilePath][$line] = true;

        self::assert(func_num_args() <= 2);
        self::assert(is_array($invokerFilePaths) || is_string($invokerFilePaths));
        self::assert(is_bool($enableUnitTest));

        if (!$enableUnitTest //
            && (BA::$exeMode & B::UNIT_TEST) //
            && (!isset(\BreakpointDebugging_PHPUnit::$unitTestDir) || strpos($fullFilePath, \BreakpointDebugging_PHPUnit::$unitTestDir) === 0) //
        ) {
            return;
        }
        // If project work directory does not exist.
        if (!isset(parent::$pwd)) {
            return;
        } else {
            // Keeps the project work directory at "__destruct" and shutdown.
            chdir(parent::$pwd);
        }
        if (!isset(self::$_includePaths)) {
            self::$_includePaths = ini_get('include_path');
            self::$_includePaths = explode(PATH_SEPARATOR, self::$_includePaths);
        }
        if (is_array($invokerFilePaths)) {
            foreach ($invokerFilePaths as $invokerFilePath) {
                if (self::_checkInvokerFilePath(self::$_includePaths, $invokerFilePath, $fullFilePath)) {
                    return;
                }
            }
            // @codeCoverageIgnoreStart
        } else {
            // @codeCoverageIgnoreEnd
            if (self::_checkInvokerFilePath(self::$_includePaths, $invokerFilePaths, $fullFilePath)) {
                return;
            }
        }
        $class = '';
        $function = '';
        if (array_key_exists('class', $callStack[$key])) {
            $class = $callStack[$key]['class'] . '::';
        }
        if (array_key_exists('function', $callStack[$key])) {
            $function = $callStack[$key]['function'];
        }
        parent::breakpoint("'$class$function()' must not invoke in '$fullFilePath' file.", debug_backtrace());
        self::callExceptionHandlerDirectly("'$class$function()' must not invoke in '$fullFilePath' file.", 4);
        // @codeCoverageIgnoreStart
    }

    // @codeCoverageIgnoreEnd
    /**
     * Throws exception if assertion is false. Also, has identification code for debug unit test.
     *
     * @param bool $assertion Assertion.
     * @param int  $id        Exception identification number inside function.
     *                        I recommend from 0 to 99 if you do not detect by unit test.
     *                        I recommend from 100 if you detect by unit test.
     *                        This number must not overlap with other assertion or exception identification number inside function.
     *
     * @return void
     * @usage
     *      \BreakpointDebugging::assert(<judgment expression>[, <identification number inside function>]);
     *      It is possible to assert that <judgment expression> is "This must be". Especially, this uses to verify a function's argument.
     *      Example: \BreakpointDebugging::assert(3 <= $value && $value <= 5); // $value should be 3-5.
     *      Caution: Don't change the value of variable in "\BreakpointDebugging::assert()" function because there isn't executed in case of release.
     */
    static function assert($assertion, $id = null)
    {
        $paramNumber = func_num_args();
        if ($paramNumber > 2) {
            self::callExceptionHandlerDirectly('Parameter number mistake.', 1);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        if (!is_bool($assertion)) {
            self::callExceptionHandlerDirectly('Assertion must be bool.', 2);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        if (!is_int($id) //
            && !is_null($id) //
        ) {
            self::callExceptionHandlerDirectly('Exception identification number must be integer.', 3);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        if (!$assertion) {
            if ($paramNumber === 1) {
                // For breakpoint debugging.
                parent::breakpoint('Assertion failed.', debug_backtrace());
            }
            // For "@expectedExceptionMessage" annotation of "DEBUG_UNIT_TEST" mode.
            self::callExceptionHandlerDirectly('Assertion failed.', $id);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * This changes a character sets to display a multibyte character string with local window of debugger, and this returns it.
     *
     * <pre>
     * Example:
     *
     * <code>
     *      $gDebugValue = \BreakpointDebugging::convertMbStringForDebug('SJIS', $scalar1, $array2, $scalar2);
     * </code>
     *
     * </pre>
     *
     * @return array Some changed variables.
     */
    static function convertMbStringForDebug()
    {
        // In case of local.
        if (!(BA::$exeMode & B::REMOTE)) {
            // Character set string to want to display, and some variables.
            $mbStringArray = func_get_args();
            $mbParamArray = array_slice($mbStringArray, 1);
            return self::_convertMbStringForDebugSubroop($mbStringArray[0], $mbParamArray);
        }
    }

    /**
     * This changes a multibyte character string array, and this returns it.
     *
     * @param string $charSet      Character set.
     * @param array  $mbParamArray Parameter array.
     *
     * @return array This does return multibyte character string for display.
     */
    private static function _convertMbStringForDebugSubroop($charSet, $mbParamArray)
    {
        self::assert(func_num_args() === 2);
        self::assert(is_string($charSet));
        self::assert(is_array($mbParamArray));

        $displayMbStringArray = array ();
        $count = 0;
        foreach ($mbParamArray as $mbString) {
            if (is_array($mbString)) {
                $displayMbStringArray[$count] = self::_convertMbStringForDebugSubroop($charSet, $mbString);
            } else if (is_string($mbString)) {
                $displayMbStringArray[$count] = mb_convert_encoding($mbString, $charSet, 'auto');
            } else {
                $displayMbStringArray[$count] = $mbString;
            }
            $count++;
        }
        return $displayMbStringArray;
    }

    /**
     * "ini_set()" with validation except for release mode.
     * Sets with "ini_set()" because "php.ini" file and ".htaccess" file isn't sometimes possible to be set on sharing server.
     *
     * @param string $phpIniVariable "php.ini" variable.
     * @param string $setValue       Value of variable.
     * @param bool   $doCheck        Does this class method check to copy to the release file?
     *
     * @return void
     */
    static function iniSet($phpIniVariable, $setValue, $doCheck = true)
    {
        self::assert(func_num_args() <= 3);
        self::assert($phpIniVariable !== 'error_log');
        self::assert(is_string($phpIniVariable));
        self::assert(is_string($setValue));
        self::assert(is_bool($doCheck));

        $getValue = ini_get($phpIniVariable);
        if ($setValue !== $getValue) {
            // In case of remote debug.
            if ($doCheck === true) {
                $dirName = BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME;
                $displayString = <<<EOD
### "\BreakpointDebugging::iniSet()": You must copy from "{$dirName}[package name]_MySetting_InDebug.php" to user place folder of "{$dirName}[package name]_MySetting.php" for release because set value and value of php.ini differ.
EOD;
                parent::ini('_MySetting_InDebug.php', self::$_onceFlagPerPackageInDebug, $displayString);
            }
            if (ini_set($phpIniVariable, $setValue) === false) {
                throw new \BreakpointDebugging_ErrorException('"ini_set()" failed.', 101);
            }
        }
    }

    /**
     * Executes function by parameter array, then displays executed function line, file, parameters and results.
     * Does not exist in case of release because this method uses for a function verification display.
     *
     * <pre>
     * Example:
     *
     * <code>
     *      $return = \BreakpointDebugging::displayVerification('function_name', func_get_args());
     *      $return = \BreakpointDebugging::displayVerification('function_name', array($object, $resource, &$reference));
     * </code>
     *
     * </pre>
     *
     * @param string $functionName Function name.
     * @param array  $params       Parameter array.
     *
     * @return Executed function result.
     */
    static function displayVerification($functionName, $params)
    {
        self::assert(func_num_args() === 2);
        self::assert(is_string($functionName));
        self::assert(is_array($params));

        $functionVerificationHtmlFileContent = <<<EOD
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>functionVerification</title>
    </head>
    <body style="background-color: black; color: white; font-size: 25px">
        <pre></pre>
    </body>
</html>
EOD;
        BW::virtualOpen(__CLASS__, $functionVerificationHtmlFileContent);
        ob_start();

        self::$tmp = $params;
        $paramNumber = count($params);
        $propertyNameToSend = '\BreakpointDebugging::$tmp';
        $callStackInfo = debug_backtrace();
        echo '<b>Executed function information.</b></br></br>';
        echo "<b>FILE</b> = {$callStackInfo[0]['file']}</br>";
        echo "<b>LINE</b> = {$callStackInfo[0]['line']}</br>";
        echo '<b>NAME</b> = ' . $functionName . '(';
        $paramString = array ();
        for ($count = 0; $count < $paramNumber; $count++) {
            $paramString[] = $propertyNameToSend . '[' . $count . ']';
            var_dump($params[$count]);
        }
        echo ')';
        $code = $functionName . '(' . implode(',', $paramString) . ')';
        $return = eval('$return = ' . $code . '; echo "<br/><b>RETURN</b> = "; var_dump($return); return $return;');
        echo '//////////////////////////////////////////////////////////////////////////////////////';

        BW::htmlAddition(__CLASS__, 'pre', 0, ob_get_clean());

        return $return;
    }

    ///////////////////////////// For package user until here in case of debug mode. /////////////////////////////
}

// When "Xdebug" does not exist.
if (!B::getXebugExists()) {
    global $_BreakpointDebugging_EXE_MODE;

    if (!($_BreakpointDebugging_EXE_MODE & B::REMOTE)) { // In case of local.
        exit(
            '<pre>'
            . '### ERROR ###' . PHP_EOL
            . 'FILE: ' . __FILE__ . ' LINE: ' . __LINE__ . PHP_EOL
            . '"Xdebug" extension has been not loaded though this is a local host.' . PHP_EOL
            . '"Xdebug" extension is required because (uses breakpoint, displays for fatal error and avoids infinity recursive function call).' . PHP_EOL
            . '</pre>'
        );
    }
}

B::checkPathEnvironmentVariable();
register_shutdown_function('\BreakpointDebugging::checkSuperUserExecution');
