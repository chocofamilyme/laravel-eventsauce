
# LaravelEventSauce

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![StyleCI][ico-styleci]][link-styleci]

Laravel wrapper for [Eventsauce](https://eventsauce.io/). Before using this package you should already know how to work with EventSauce.

## Requirements

- PHP ^7.2
- Laravel ^5.8

## Installation

Via Composer

```bash
composer require chocofamilyme/laravel-eventsauce
```
## Migrations

You can publish and modify default migrations tables (`domain_messages`, `snapshots`) with the following command:

```bash
php artisan vendor:publish --tag="eventsauce-migrations"
php artisan migrate
```


## Configuration

You can publish the config file with the following command:

```bash
php artisan vendor:publish --tag="eventsauce-config"
```

#### Code Generation

Types, commands and events can be generated starting from a yaml file. Here you can specify the input and the output of the code generation. More info on code generation here: https://eventsauce.io/docs/getting-started/create-events-and-commands

#### Default Connection

The default database connection can be modified by setting the `EVENTSAUCE_CONNECTION` env variable:

```dotenv
EVENTSAUCE_CONNECTION=mysql
```

#### Default Message Table

The default table name for your domain messages can be set with the `EVENTSAUCE_TABLE` env variable:

```dotenv
EVENTSAUCE_TABLE=domain_messages
```

#### Default Snapshot Table

The default table name used to store snapshots can be set with the `EVENTSAUCE_SNAPSHOT_TABLE` env variable:

```dotenv
EVENTSAUCE_SNAPSHOT_TABLE=snapshots
```

#### Default Message Repository

This class will be used to store messages. You may change this to any class that implements `EventSauce\EventSourcing\MessageRepository` , by default used `Chocofamily\LaravelEventSauce\MessageRepository::class`

#### Default Snapshot Repository

This class will be used to store snapshots. You may change this to any class that implements `EventSauce\EventSourcing\Snapshotting\SnapshotRepository` , by default used `Chocofamily\LaravelEventSauce\SnapshotRepository::class`

#### Default Consumer Handler

This class will be used to put message on the handlers, by default used `Chocofamily\LaravelEventSauce\ConsumerHandler::class`

## Usage
TODO

## License

license. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/chocofamilyme/laravel-eventsauce.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/chocofamilyme/laravel-eventsauce.svg?style=flat-square
[ico-styleci]: https://github.styleci.io/repos/225345376/shield

[link-packagist]: https://packagist.org/packages/chocofamilyme/laravel-eventsauce
[link-downloads]: https://packagist.org/packages/chocofamilyme/laravel-eventsauce
[link-styleci]: https://github.styleci.io/repos/225345376
[link-author]: https://github.com/chocofamily
[link-contributors]: ../../contributors
