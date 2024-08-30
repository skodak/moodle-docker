# OrbStack Docker containers for Moodle development

*__NOTE: This is a fork of Moodle Docker, it is maintained by Petr Skoda.__*

This repository contains Docker configuration for OrbStack aimed at Moodle developers and testers to easily deploy a development
or testing environment for Moodle or any other fork of Moodle.

## Prerequisites
* Computer with Apple macOS
* [OrbStack](https://orbstack.dev/) must be installed

## Why OrbStack?

* better performance than Docker Desktop for Mac
* container domains with https instead of confusing port forwarding
* native macOS app

## Features:
* Supported database servers - PostgreSQL, MariaDB and MySQL (MS SQL Server might work only on amd64 platforms)
* Behat/Selenium configuration for Chromium, Chrome, Edge and Firefox
* Catch-all smtp server and web interface to messages using [Mailpit](https://github.com/axllent/mailpit)
* All PHP Extensions enabled configured for external services (e.g. solr, ldap)
* All supported PHP versions
* Configuration is done via _./moodle-docker.env_ file and optional environment variables
* Full support for macOS with Apple M-series CPUs
* Windows and Linux are not supported

## Quick start

1. Open terminal and cd to your projects directory
2. Clone __moodle-docker__ repository `https://github.com/skodak/moodle-docker.git`
3. Clone __moodle__ repository `https://github.com/moodle/moodle.git`
4. Either delete/rename your existing Moodle config.php file if present,
or add following code to the very start of your config.php to use default docker config:
```php
<?php  // Moodle configuration file
if (getenv('MOODLE_DOCKER_RUNNING', true)) {
    require('/var/www/config-docker.php');
    return;
}
```
5. Create __moodle-docker.env__ file with the following content in your moodle directory
   (or you can copy _moodle-docker/templates/moodle-docker.env_ to moodle directory as a starting point):
```
# Specifies database type
MOODLE_DOCKER_DB=pgsql
```
6. Add `moodle-docker/bin` to your search path:
```bash
export PATH=$PATH:/path/to/moodle-docker/bin
```
7. Open terminal, cd to your moodle directory and execute `mdc-up` script:
```bash
cd /path/to/moodle
mdc-up
```
8. Now you can complete the test site installation at [https://webserver.moodle.orb.local/](https://webserver.moodle.orb.local/).
9. Alternatively you can complete the test site installation from CLI:
```bash
cd /path/to/moodle
site-install --agree-license --adminpass="test"
```
10. You can view emails which Moodle has sent out at [https://mailpit.moodle.orb.local/](https://mailpit.moodle.orb.local/).
11. When you are finished with testing you can delete the instances using `mdc-down` script:
```bash
cd /path/to/moodle
mdc-down
```

## Run several Moodle instances

By default, docker compose uses current directory name as project name,
which means that you do not have to add COMPOSE_PROJECT_NAME to
__moodle-docker.env__ file when it is inside your moodle code directory.

However, you need to specify unique ports of each service that is exposed.
For example, you could add following to __moodle-docker.env__ file
prior to running `mdc-up` script where ports are incremented by one
for each of your moodle checkout directories.

First Moodle project:
```
# Specifies database type
MOODLE_DOCKER_DB=pgsql
```

Second Moodle project:
```
# Specifies database type
MOODLE_DOCKER_DB=mysql
```

You cannot have multiple docker compose instances for one moodle project directory.

## Use docker for running PHPUnit tests

To initialise the PHPUnit test environment execute `behat-init` script:

```bash
cd /path/with/moodle-docker.env/
phpunit-init
```

To run PHPUnit tests execute `phpunit` script, for example:

```bash
cd /path/with/moodle-docker.env/
phpunit --filter=auth_manual
```

You should see something like this:
```
Moodle 4.0.4+ (Build: 20220922), d708740c3fdb953a6dbb8dd2b3068de9d23a3d27
Php: 7.4.30, pgsql: 12.12 (Debian 12.12-1.pgdg110+1), OS: Linux 5.10.124-linuxkit aarch64
PHPUnit 9.5.13 by Sebastian Bergmann and contributors.

.....                                                               5 / 5 (100%)

Time: 00:00.627, Memory: 290.00 MB

OK (5 tests, 17 assertions)
```

Notes:
* If you want to run tests with code coverage reports:
```bash
cd /path/with/moodle-docker.env/
# Build component configuration
phpunit-util --buildcomponentconfigs
# Execute tests for component
mdc exec webserver php -d pcov.enabled=1 -d pcov.directory=. vendor/bin/phpunit --configuration reportbuilder --coverage-text
```
* See available [Command-Line Options](https://phpunit.readthedocs.io/en/9.5/textui.html#textui-clioptions) for further info

## Use docker for running Behat tests

NOTE: On Macs with M processor configure MDC to use Chromium browser instead of Chrome.

```
# Specifies Chromium to be used in behat
MOODLE_DOCKER_BROWSER=chromium:4.23.1
```

To initialise the Behat test environment execute `behat-init` script: 

```bash
cd /path/with/moodle-docker.env/
bahat-init
```

To run Behat tests execute `behat` script, for example:

```bash
cd /path/with/moodle-docker.env/
behat --tags=@auth_manual
```

You should see something like this:
```
Moodle 4.0.4+ (Build: 20220922), d708740c3fdb953a6dbb8dd2b3068de9d23a3d27
Php: 7.4.30, pgsql: 12.12 (Debian 12.12-1.pgdg110+1), OS: Linux 5.10.124-linuxkit aarch64
Run optional tests:
- Accessibility: No
Server OS "Linux", Browser: "firefox"
Started at 29-09-2022, 00:58
...............

2 scenarios (2 passed)
15 steps (15 passed)
0m42.66s (51.78Mb)
```

Notes:

* The behat faildump directory is exposed at https://webserver.moodle.orb.local/_/faildumps/.
* Use `MOODLE_DOCKER_BROWSER` to switch the browser you want to run the test against.
  You need to recreate your containers using `mdc-rebuild`,
  if you make any changes in __moodle-docker.env__ file.

### Using VNC to view Behat tests

If you want to observe the execution of scenarios in a web browser then
just connect to selenium container using OrbStack container domain name.

You should be able to use any kind of VNC viewer, such as [Real VNC Viewer](https://www.realvnc.com/en/connect/download/viewer/)
or standard macOS application _Screen Sharing_.

With the containers running, enter selenium.moodle.orb.local:5900 as the port in VNC Viewer or type [vnc://selenium.moodle.orb.local:5900](vnc://127.0.0.1:5900) address
in _Screen Sharing_ application. You will be prompted for a password, the password is 'secret'.

You should be able to see an empty Desktop. When you run any Behat tests with @javascript tag
a browser will pop up, and you will see the tests execute.

### Using Browser debug console to view Behat tests in headless Chrome

Make sure config.php behat settings match [template](templates/config.php)

```
MOODLE_DOCKER_BROWSER=chromium:4.23.1
# Instruct Chrome to expose a debugging port - unfortunately orbStack domains do not seem to work in 
MOODLE_DOCKER_BROWSER_DEBUG_PORT=9229

```

Run port forwarder from selected port in MOODLE_DOCKER_BROWSER_DEBUG_PORT=9229 to internal 9222:
```bash
cd /path/to/moodle
selenium-debug
```

1. Open Chrome and go to chrome://inspect
2. add 127.0.0.1:9229
3. start behat run
3. Click on Remote Target link with your session 

## Use docker to run grunt

First you need to install appropriate node and npm version in webserver container, for example:

```bash
cd /path/with/moodle-docker.env/
node-init
```

To run grunt use:

```bash
cd /path/with/moodle-docker.env/
grunt
```

## Stop and restart containers

`mdc-down` which was used above after using the containers stops and destroys the containers.
If you want to use your containers continuously for manual testing or development without starting them up
from scratch everytime you use them, you can also just stop without destroying them.
With this approach, you can restart your containers sometime later,
they will keep their data and won't be destroyed completely until you run `mdc-down`.

```bash
cd /path/with/moodle-docker.env/

# Stop containers
mdc-stop

# Restart containers
mdc-start
```

It is also possible to use Dashboard in Docker Desktop to stop, start or delete the instances. 

## Environment variables file _./moodle-docker.env_

You can change the configuration of the docker images by setting various environment variables in __moodle-docker.env__ file.
This file is usually placed in your Moodle code directory, however it can be placed in any directory because the bin
scripts are looking for it in the current working directory when executed.

Changes in the environment file should be done **before** calling `mdc-up`. If your containers are running
first call `mdc-down`, then update the environment file and finally start the containers again.

| Environment Variable                      | Mandatory | Allowed values                                                     | Default value                                           | Notes                                                                                                                                                            |
|-------------------------------------------|-----------|--------------------------------------------------------------------|---------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `MOODLE_DOCKER_DB`                        | yes       | pgsql, mariadb, mysql, mssql                                       | none                                                    | The database server to run against                                                                                                                               |
| `MOODLE_DOCKER_WWWROOT`                   | no        | path on your file system                                           | current directory if ./_moodle-docker.env_ file exists  | Useful for non-moodle projects and when Moodle dirroot is not in the same directory as _moodle-docker.env_ file                                                  |
| `MOODLE_DOCKER_DB_VERSION`                | no        | Docker tag - see relevant database page on docker-hub              | mysql: 8.4 <br/>pgsql: 16 <br/>mariadb: 11.4 <br/>mssql | The database server docker image tag                                                                                                                             |
| `MOODLE_DOCKER_PHP_VERSION`               | no        | 8.1, 8.0, 8.2, 8.3                                                 | 8.1                                                     | The php version to use                                                                                                                                           |
| `MOODLE_DOCKER_BROWSER`                   | no        | firefox, chrome, chromium  firefox:&lt;tag&gt;, chrome:&lt;tag&gt; | firefox:4                                               | The browser to run Behat against. Supports a colon notation to specify a specific Selenium docker image version to use.                                          |
| `MOODLE_DOCKER_PHPUNIT_EXTERNAL_SERVICES` | no        | any value                                                          | not set                                                 | If set, dependencies for memcached, redis, solr, and openldap are added                                                                                          |
| `MOODLE_DOCKER_BBB_MOCK`                  | no        | any value                                                          | not set                                                 | If set the BigBlueButton mock image is started and configured                                                                                                    |
| `MOODLE_DOCKER_BEHAT_FAILDUMP`            | no        | Path on your file system                                           | not set                                                 | Behat faildumps are already available at https://webserver.moodle.orb.local/_/faildumps/ by default, but you can specify a different directory outside of Docker |
| `MOODLE_DOCKER_PHP_ERROR_LOG`             | no        | Path to PHP error log on your file system                          | not set                                                 | You can specify a different PHP error logging file outside of Docker                                                                                             |
| `MOODLE_DOCKER_BACKUPS`                   | no        | Path to backup directory on your file system                       | not set                                                 |                                                                                                                                                                  |
| `COMPOSE_PROJECT_NAME`                    | no        | must be unique                                                     | current directory name                                  | Must be set if multiple instances are active and _./moodle-docker.env_ is not used, this is used as second part of the container domain                          |

In addition to that, `MOODLE_DOCKER_RUNNING=1` env variable is defined and available
in the webserver container to flag being run by `mdc`. Developer
can use this to conditionally make changes in `config.php`. The common case is
to load test-specific configuration:
```
// Load moodle-docker config file if we are in moodle-docker environment
if (getenv('MOODLE_DOCKER_RUNNING')) {
    require_once($CFG->dirroot . '/config.docker-template.php');
}

require_once($CFG->dirroot . '/lib/setup.php'); // Do not edit.
```

## Local customisations

In some situations you may wish to add local customisations, such as including additional containers, or changing existing containers.

This can be accomplished by specifying a `local.yml`, which will be added in and loaded with the existing yml configuration files automatically. For example:

``` file="local.yml"
services:

  # Add the adminer image at the latest tag on port 8080:8080
  adminer:
    image: adminer:latest
    restart: always
    ports:
      - 8080:8080
    depends_on:
      - "db"

  # Modify the webserver image to add another volume:
  webserver:
    volumes:
      - "/opt/data:/opt/data:cached"
```
## Extra compose configuration file _./moodle-docker.yml_

Instead of environmental variables it is also possible to supply extra compose configuration file.

For example if you want to use private git repositories from containers you can to add SSH keys
by creating __moodle-docker.yml__ file in current directory:

```yml
services:
  webserver:
    volumes:
    - /path/to/docker/ssh/id_ed25519:/root/.ssh/id_ed25519:ro
    - /path/to/docker/ssh/id_ed25519.pub:/root/.ssh/id_ed25519.pub:ro
```

Another example override when you want to run `MOODLE_DOCKER_PHP_VERSION=5.6` with `MOODLE_DOCKER_DB=mssql`:

```yml
services:
  webserver:
    environment:
      MOODLE_DOCKER_DBTYPE: mssql
```

Changes in the configuration file should be done **before** calling `mdc-up`. If your containers are running
first call `mdc-down`, then update the configuration file and finally start the containers again.

## Using XDebug for live debugging

The XDebug PHP Extension is not included in this setup and there are reasons not to include it by default.

However, if you want to work with XDebug, especially for live debugging, you can add XDebug to a running webserver container easily:

```bash
# Install XDebug extension with PECL
mdc exec webserver pecl install xdebug

# Set some wise setting for live debugging - change this as needed
read -r -d '' conf <<'EOF'
; Settings for Xdebug Docker configuration
xdebug.mode = debug
xdebug.client_host = host.docker.internal
EOF
mdc exec webserver bash -c "echo '$conf' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"

# Enable XDebug extension in Apache and restart the webserver container
mdc exec webserver docker-php-ext-enable xdebug
mdc restart webserver
```

While setting these XDebug settings depending on your local need, please take special care of the value of `xdebug.client_host` which is needed to connect from the container to the host. The given value `host.docker.internal` is a special DNS name for this purpose within Docker for Windows and Docker for Mac. If you are running on another Docker environment, you might want to try the value `localhost` instead or even set the hostname/IP of the host directly.

After these commands, XDebug ist enabled and ready to be used in the webserver container.
If you want to disable and re-enable XDebug during the lifetime of the webserver container, you can achieve this with these additional commands:

```bash
# Disable XDebug extension in Apache and restart the webserver container
mdc exec webserver sed -i 's/^zend_extension=/; zend_extension=/' /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
mdc restart webserver

# Enable XDebug extension in Apache and restart the webserver container
mdc exec webserver sed -i 's/^; zend_extension=/zend_extension=/' /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
mdc restart webserver
```

## Companion docker images

The following Moodle customised docker images are close companions of this project:

* [moodle-php-apache](https://github.com/moodlehq/moodle-php-apache): Apache/PHP Environment preconfigured for all Moodle environments
* [moodle-db-mssql](https://github.com/moodlehq/moodle-db-mssql): Microsoft SQL Server for Linux configured for Moodle

## PhpStorm configuration

PhpStorm can be configured to use moodle-docker directly which eliminates the need to install PHP binaries,
webserver or database.

There is also a simple _Docker manager_ in Services tab in PhpStorm which can be used to stop/start the containers.

### Configure remote docker PHP CLI interpreter

First make sure that Docker Desktop setting "Use Docker Compose V2" is enabled,
if not PHPStorm will not be able to parse _moodle-docker-compose.yml_ using
_docker-compose_ executable. The option "Use Compose V2 beta" does not seem
to function properly yet, so overriding the docker-compose seems to be the only option.

Then verify moodle-docker instance is up and running - see Quick start section above.

Then open your Moodle project directory in PhpStorm and add a remote PHP CLI interpreter:

1. Open "Preferences / PHP"
2. Add new _CLI Interpreter_ by clicking "..."
3. Click "+" and select "From Docker, Vagrant, VM, WSL, remote..."
4. Select existing docker server or click "Docker compose" and press "New..."  in "Server:" field
5. Select __./moodle-docker-compose.yml__ file in "Configuration files:" field
6. Select __webserver__ in "Service:" field
7. Press "OK"
8. Switch lifecycle to __Connect to existing container ('docker-compose exec')__
9. Press reload icon in "PHP executable:" field, PhpStorm should detect correct PHP binary
10. You should customise the interpreter name at the top and make it "Visible only for this project"
11. Press "OK" to save interpreter settings
12. Verify the new interpreter is selected in "CLI Interpreter:" field
13. Press "OK" to save PHP settings

### Configure remote PHPUnit interpreter

First make sure your docker compose instance is running and PHPUnit was initialised.
The remote PHP CLI interpreter must be already configured in your PhpStorm.

1. Open "Preferences / PHP / Test Frameworks"
2. Click "+" and select "PHPUnit by remote interpreter"
3. Select your docker interpreter that was created for this project and press "OK"
4. Verify "Path to script:" field is set to `/var/www/html/vendor/autoload.php`
5. Verify "Default configuration file:" field is enabled and set it to `/var/www/html/phpunit.xml`
6. Press "Apply" and verify correct PHPUnit version was detected
7. Press "OK"

You may want to delete all unused interpreters.
To execute PHPUnit tests open a testcase file and click on a green arrow gutter icon.

### Configure remote Behat interpreter

First make sure your docker compose project is running and Behat was initialised.
The remote PHP CLI interpreter must be already configured in your PhpStorm.

1. Open "Preferences / PHP / Test Frameworks"
2. Click "+" and select "Behat by remote interpreter"
3. Select your docker interpreter that was created for this project and press "OK"
4. Set "Path to Behat executable:" field to `/var/www/html/vendor/behat/behat/bin/behat`
5. Enable "Default configuration file:" field and se it to `/var/www/behatdata/behatrun/behat/behat.yml`
6. Press "Apply" and verify correct Behat version was detected - if not check the instance is running and Behat was initialised manually
7. Press "OK"

You may want to delete all unused interpreters.
To execute Behat tests open a feature file and click on a green arrow gutter icon.
If you have configured a VNC port then you can watch the scenario progress in your VPN client.

### Connect PhpStorm to docker database

You can connect to moodle database directly using container domains.

Make sure your docker compose project is running and test site was initialised.

Then setup new database connection in PhpStorm through the exposed port, for example:

1. Open "Database" tab in PhpStorm
2. Press "+" and select "Database source / PostgreSQL"
3. Set "User:" field to 'moodle'
4. Set "Password:" field to 'm@0dl3ing'
5. Set "Database:" field to 'moodle'
6. Set "Host:" to db.moodle.orb.local, keep "Port:" to default database port 
7. Press "OK"
8. Refresh the database metadata
9. Open "Preferences / Language & Frameworks / SQL Dialects"
10. Set "Project SQL Dialect:" field to 'PostgreSQL'
11. Press "OK"
12. Copy __.phpstorm.meta.php__ file from `moodle-docker/templates/` directory into your Moodle project
13. You may need to use "File / Invalidate caches..." and restart the IDE

As a test open lib/accesslib.php and find some full SQL statement and verify the SQL syntax
is highlighted and SQL syntax errors are detected.

## Visual Studio Code configuration

1. Install _Docker_ extension from Microsoft
2. Add __moodle-docker.env__ to your Moodle project
3. Create docker instance with `mdc-up`

### Use _Better PHPUnit_ to run tests in VSCode

1. Initialise phpunit with `phpunit-init`
2. Install _Better PHPUnit_ extension
3. Update VSCode configuration (note you need to edit project path):
```json
{
    "better-phpunit.docker.command": "docker compose -f moodle-docker-compose.yml exec webserver",
    "better-phpunit.docker.enable": true,
    "better-phpunit.phpunitBinary": "vendor/bin/phpunit",
    "better-phpunit.docker.paths": {
        "/path/to/your/moodle": "/var/www/html"
    },
    "better-phpunit.xmlConfigFilepath": "/var/www/html/phpunit.xml"
}
```
4. Open a test file, go to method and press Cmd+shif+p and select one of _Better PHPUnit_ options to run tests.

## Devdocs in moodle-docker

1. Checkout https://github.com/moodle/devdocs.git code into some directory
2. Add `moodle-docker.env` file:
```
COMPOSE_PROJECT_NAME=devdocs
MOODLE_DOCKER_WWWROOT=/path/to/devdocs

MOODLE_DOCKER_PHP_VERSION=8.1
MOODLE_DOCKER_DB=pgsql
```
3. Add `moodle-docker.yaml` file:
```yaml
services:
  webserver:
    ports:
      - "127.0.0.1:3000:3000"
```
4. Execute `mdc-up`, `mdc-bash`
5. Install nvm:
```shell
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.3/install.sh | bash
```
6. Exit bash and `mdc-bash` again
7. Execute commands:
```shell
nvm install
npm i -g yarn
yarn
yarn build
yarn start --host=0.0.0.0
```
8. Open [http://localhost:3000/devdocs/](http://localhost:3000/devdocs/)

## Contributions

Are extremely welcome!
