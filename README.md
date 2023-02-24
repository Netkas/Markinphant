# Markinphant Telegram Bot

Markinphant bot is a rewrite of [DavideGalilei/markinim](https://github.com/DavideGalilei/markinim) done in php/ncc as
a demonstration of how to use the various combinations of libraries and tools that are made available by Nosial.

Markinphant Bot works on the simple principle of Markov chains, it takes a message and generates a new message based on
the words in the message. The bot is able to learn from the messages it receives and generate new messages based on
the messages it has learned. The result being a somewhat funny bot that can generate new messages that could appear
silly.

The PHP implementation of Markov chains is based off [bit0r1n/nimkov](https://github.com/bit0r1n/nimkov) with some
minor improvements to the implementation.

## Table of contents

<!-- TOC -->
* [Markinphant Telegram Bot](#markinphant-telegram-bot)
  * [Table of contents](#table-of-contents)
  * [Installation for dummies](#installation-for-dummies)
    * [There was an issue!](#there-was-an-issue-)
  * [Installation](#installation)
  * [Compile from source](#compile-from-source)
  * [Requirements](#requirements)
  * [Configuration](#configuration)
    * [Telegram Bot Configuration](#telegram-bot-configuration)
    * [Redis Configuration](#redis-configuration)
    * [Tamer Configuration](#tamer-configuration)
  * [Data Storage](#data-storage)
  * [License](#license)
<!-- TOC -->

## Installation for dummies

Read this if you are a newbie and don't know how to install this bot.

1. Install [ncc](https://git.n64.cc/nosial/ncc)
2. Run `ncc install -p "netkas/markinphant=latest@n64"` as **root**
3. Run `configlib --conf markinphant --editor nano` and edit the configuration file to your liking *(No need to run as root)*
4. Save the configuration file and run `markinphant` to start the bot

 > Tip: You can pass on `--log-level debug` to the `markinphant` command to see pretty debug messages

### There was an issue!

Copy the error message and open an issue on the [issue tracker](https://git.n64.cc/netkas/markinphant/issues). 
Thanks!

## Installation

The library can be installed using ncc:

```bash
ncc install -p "netkas/markinphant=latest@n64"
```

## Compile from source

To compile the library from source, you need to have [ncc](https://git.n64.cc/nosial/ncc) installed, then run the
following command:

```bash
ncc build
```

## Requirements

The library requires PHP 8.0 or higher.

## Configuration

After installing the bot, it is recommended to start the bot for the first time by running the following command:

```bash
markinphant
```

or

```bash
ncc exec --package="com.netkas.markinphant"
```

This will generate a configuration file with the default values to which you can edit using [ConfigLib](https://git.n64.cc/nosial/libs/config).

 > Note: You can edit the configuration file directory by running `configlib --conf markinphant --editor nano`

### Telegram Bot Configuration

The bot token can be obtained by talking to [@BotFather](https://t.me/BotFather) on Telegram.
Then, you can set the token in the configuration file by running the following command:

```bash
configlib --conf markinphant --prop bot.token --value <token>
```

If you are using a custom server, you can configure the bot to use a different host

```bash
configlib --conf markinphant --prop bot.host --value api.telegram.org
configlib --conf markinphant --prop bot.use_ssl --value True
```

### Redis Configuration

The bot uses redis to store sessions and models to reduce memory usage and to allow multiple workers to access the same
data. The redis server can be configured by running the following commands:

```bash
configlib --conf markinphant --prop redis.host --value localhost
configlib --conf markinphant --prop redis.port --value 6379
configlib --conf markinphant --prop redis.password --value <password>
```

### Tamer Configuration

This bot uses Tamer to allow the execution of parallel tasks, to configure Tamer to use a supported messaging queue server,
you can run the following commands:

```bash
configlib --conf markinphant --prop tamer.enabled --value True
configlib --conf markinphant --prop tamer.protocol --value gearman
configlib --conf markinphant --prop tamer.port --value 4730
```

 > Note: it is recommended to use an editor to edit the servers value, as it only accepts arrays of strings, for example `["host:port", "host:port"]`

To edit the configuration file using nano, you can run the following command:

```bash
configlib --conf markinphant --editor nano
```


## Data Storage

Sessions & models are stored in `/var/ncc/data/com.netkas.markinphant` and are occasionally maintained and cleaned up
by automated tasks built into the bot. Disabling automatic maintenance will result in a build up of data over time and
poor performance. If someone wants to contribute a PR to implement database storage, I'd be happy to review it.

When you start the bot, the main program will initialize the session manager and load all sessions from disk onto the
redis server. Any changes made to the sessions while the bot is running is made in the redis server and then saved every
minute to disk. This approach is used to ensure that workers aren't using up additional memory by keeping redundant copies
of the sessions in memory.


## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details