[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/realshadow/satis-control-panel/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/realshadow/satis-control-panel/?branch=master) [![Build Status](https://scrutinizer-ci.com/g/realshadow/satis-control-panel/badges/build.png?b=master)](https://scrutinizer-ci.com/g/realshadow/satis-control-panel/build-status/master) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/319e153a-ab8e-4532-ab6e-e796475a4900/small.png)](https://insight.sensiolabs.com/projects/319e153a-ab8e-4532-ab6e-e796475a4900)

# Satis Control Panel

Satis Control Panel (SCP) is a simple web UI for managing your [Satis Repository](https://github.com/composer/satis) for
 [Composer Packages](https://getcomposer.org/).
 
SCP backend is written in Laravel and with a React + Typescript combo. 

## Features

* simple UI for managing your Satis configuration file for both - private packages and public packages mirrored from [Packagist](https://packagist.org/)
* no database required - only PHP and optional Nodejs server for automatic generation of Satis configuration file
* RESTful API for integration with CI services
* SCP comes with Atlassian plugins for Bamboo and Stash to ease managing package building 
* Cron job for automatic build of public packages mirrored from [Packagist](https://packagist.org/)

## Installation

You can install SCP directly with Composer by running

```
composer create-project realshadow/satis-control-panel [--stability-dev]
```

After that you can rename `example.env` to `.env` and set required configuration options. 

### Building javascript
```
npm run build

// or

npm run build-win
```

During development you can start Webpack dev server with

```
npm start
```

or run Gulp watcher for `less` files with

```
gulp watch
```

### Satis configuration file

In `resources/` directory you will find `satis.json.dist` file which holds default Satis configuration, copy this file and
rename it to `satis.json` and edit the `name` and `homepage` property.

```
cp resources/satis.json.dist resources/satis.json
```

When you are done, you have to set correct permissions for your configuration file for web user. E.g. www-data, should be 
able to read/write this file). More in next *Permissions* section.

### Permissions

For building to work correctly you have to set correct permissions to few directories/directories:

* bootstrap/cache/
* storage/
* public/private/
* public/public/
* resources/satis.json

Each directory/file should be readable/writable by web user, e.g. www-data. For example:

```
chmod -R 777 bootstrap/cache storage public/private public/public
chmod 777 resources/satis.json
```

### Visiting your control panel

Now you can visit your control panel at `http://{host}/control-panel`.

## Configuration options

Here is a list of configuration options that can be set in `config/satis.php` (some of them can be set in `.env` file as well for convenience):

|             Option | Description                                                                                                                                                                            | Default value          | Can be set in `.env` |
|-------------------:|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|------------------------|----------------------|
| config             | Path to satis configuration file                                                                                                                                                       | resources/satis.json   | Yes                  |
| composer_home      | Composer home directory (thi                                                                                                                                                           | storage/composer       | Yes                  |
| composer_cache     | Composer cache directory                                                                                                                                                               | storage/composer/cache | Yes                  |
| memory_limit       | Memory limit that will be set before running Satis build command                                                                                                                       | 2G                     | No                   |
| build_verbosity    | Verbosity of Satis build command (more info will be stored in logs)                                                                                                                    | vvv                    | No                   |
| private_repository | Directory where Satis will generate private repository. This also serves as a way to distinguish public and private repositories in repository address, e.g. satis.example.com/private | private                | No                   |
| public_repository  | Directory where Satis will generate public repository. This also serves as a way to distinguish public and private repositories in repository address, e.g. satis.example.com/public   | public                 | No                   |
| proxy.http         | Proxy address that will be used by Satis/Composer for HTTP requests                                                                                                                    | null                   | Yes                  |
| proxy.https        | Proxy address that will be used by Satis/Composer for HTTPS requests                                                                                                                   | null                   | Yes                  |

**Note:** if you change the default directory, remember to set correct permissions for your new directory.

## How it works

SCP manages a single Satis configuration file which is generated on the fly when specific UI actions are performed. 
During each generation cycle the file is split into public and private repository configuration file, because private
packages use funcionality that doesn't work well with Packagist (it will try to mirror whole Packagist repository).

Besides adding, editing and removing packages/repositories from configuration file, UI allows you to build/rebuild every
package or run a complete rebuild of all registered packages/repositories.

Build process can run synchronously or asynchronously (by redirecting output to `/dev/null` and spawning a new process). By default,
all builds run asynchronously, except on Windows where they are forced to run synchronously. This can be also forced during during API
request by setting `async_mode` to `false`.

### Missing mirrored configuration files

Since the configuration files mirroring is triggered by any UI action, it is not always the correct behaviour. If you want to manually
trigger config generation, for example when you make changes directly on the server, you can trigger the config generation with this
artisan command

```
php artisan satis:make:config
```

### UI State

During build process whole UI is locked. During asynchronous builds UI state is handled by Node server, but running it is completely
optional. 

It can be started with

```
npm run server
```

and will run on port `9010` by default. This can be changed in `node/config.json` file.

If for some reason UI will stay locked even though no packages are currently being build, it can be unlocked by running:

```
php artisan satis:persister:unlock
```

### Composer auth

Composer file `auth.json` can be put in `COMPOSER_HOME` directory where you can put your Github token or credentials for
needed for private repositories.

### Private packages

Private packages are identified by repository URL address. When you will add/edit a new repository you can choose its type.
By default, all repositories are considered as `VCS` repository. Building and rebuilding is handled by `partial update` 
functionality introduced in [this PR](https://github.com/composer/satis/pull/266) only repositories that have a URL can be
managed in UI. Those include:

* vcs
* pear
* composer
* artifact
* path

Adding support for more repository types is planned in future.

Private packages use the `repositories` config key with `require-all` options set to `true`, thus all known packages are
taken out of registered repositories, which means that Packagist must be disabled by default. This is handled when configs
are split into private public part.

### Public (packagist) packages

Public packages are used for mirroring of existing packages that can be installed from Packagist if you are behind a corporate
proxy, thus speeding up overall development and deployment time.

All packages added here are *fully* mirrored with all their dependencies (but we still skip `dev-dependencies`). Currently
only one version constraint is used and that's `*` so we can get a complete packagist clone. 

Adding support for custom version constraints is planned in future.

Since full rebuild in this case could potentionally take few hours, you can use provided Cron task for a daily rebuild (see *Cron task* section).

**Note though that you should not try to mirror whole Packagist repository!**

## RESTful API

SCP comes with built in API for esier integration with your favorite CI solution. 

### Private packages

Private packages use `md5` encoded repository url as ID.

* get all repositories

```
GET control-panel/api/repository
```

* get one repository

```
GET control-panel/api/repository/{repository_id}
```

* add new repository

```
POST control-panel/api/repository
{
    'url': 'foo',
    'type: 'bar'
}
```

* update existing repository

```
PUT control-panel/api/repository/{repository_id}
{
    'url': 'foo',
    'type: 'bar'
}
```

* delete existing repository

```
DELETE control-panel/api/repository/{repository_id}
```


All methods return `HTTP 404` if no repository is found. 

**Note:** same API can be used for public packages as well by replacing `repository` by `package`. Although remote control of public packages is not necessary.

### Additional API options

During both `POST` and `PUT` requests two additional options can be provided:

* **async_mode** - *true/false* => if the build should run synchronously or asynchronously (all builds run asynchronously by default) 
* **disable_build** - *true/false* => if set to `true` Satis build command won't be run
 
## Logs

All logs can be found in `storage/logs` directory. Logs are divided into:

* *api_request.log* - logs all API requests
* *builder_async.log* - logs all builds that run asynchronously, keep in mind that each asynchronous build has its own log file in `async` subdirectory identified by its timestamp
* *builder_sync.log* - logs all builds that run synchronously
* *cron.log* - for cron task logs

## Cron task

Since mirroring of public packages can take some time and running full rebuild from UI is not a good idea, because this will lock it during the
build process, SCP comes with a built in cron task that runs daily and will rebuild all repositories. It can be triggered with a cron entry
similar to this:

```
* * * * * php /path/to/satis-folder/artisan schedule:run >> /dev/null 2>&1
```

Alternatively, you can add this cron entry:

```
00 00 * * * curl --request POST --header "Content-Length: 0" --header "X-Requested-With: XMLHttpRequest" http://{scp-url-address}/control-panel/build-public
```

This can be used for private packages as well

```
00 00 * * * curl --request POST --header "Content-Length: 0" --header "X-Requested-With: XMLHttpRequest" http://{scp-url-address}/control-panel/build-private
```

## Atlassian plugins

SCP was created in an environment which uses Atlassian Stash and Bamboo as part of CI and thus two plugins were needed to
completely integrate Composer packages into our build process.

* [Stash Satis Build Hook](https://github.com/realshadow/stash-satis-build-hook.git) - a post receive hook that will register and trigger a build/rebuild of your package in SCP (if you want to skip deployment process)
* [Bamboo Satis Build](https://github.com/realshadow/bamboo-satis-build.git) - a deployment task for rebuilding currently deployed Composer package in Satis repository
 
Both use `partial update` functionality which was introduced in [this PR](https://github.com/composer/satis/pull/266).

## TODO

* option to import composer.lock file for public packages
* option to use more types of private packages
* option to write custom version constraints for public packages
* option to see what's going during long running builds of public packages 
* better handling of race conditions during simultaneous writes/reads
* authentification? (this can be simply handled with htpasswd)
* ????

PR's are welcome

## Alternatives

* [Satis Go](https://github.com/benschw/satis-go)
* [Toran proxy](https://toranproxy.com/)
