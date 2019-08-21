# Faker Plugin

The **Faker** Plugin is an extension for [Grav CMS](http://github.com/getgrav/grav). Faker creates **dummy Grav page content** for testing and development purposes.

## Installation

Installing the Faker plugin can be done in one of three ways: The GPM (Grav Package Manager) installation method lets you quickly install the plugin with a simple terminal command, the manual method lets you do so via a zip file, and the admin method lets you do so via the Admin Plugin.

### GPM Installation (Preferred)

To install the plugin via the [GPM](http://learn.getgrav.org/advanced/grav-gpm), through your system's terminal (also called the command line), navigate to the root of your Grav-installation, and enter:

    bin/gpm install faker

This will install the Faker plugin into your `/user/plugins`-directory within Grav. Its files can be found under `/your/site/grav/user/plugins/faker`.

### Manual Installation

To install the plugin manually, download the zip-version of this repository and unzip it under `/your/site/grav/user/plugins`. Then rename the folder to `faker`. You can find these files on [GitHub](https://github.com/trilbymedia/grav-plugin-faker) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/faker
	
> NOTE: This plugin is a modular component for Grav which may require other plugins to operate, please see its [blueprints.yaml-file on GitHub](https://github.com/trilbymedia/grav-plugin-faker/blob/master/blueprints.yaml).

### Instructions

This plugin provides a **CLI Command** to create dummy/fake data.  This simplest way to run **Faker** is to simply use:

```shell script
$ bin/plugin faker generate
```

You will be prompted for several options, all of which have defaults.  Just hit `[return]` to accept the defaults, or choose your own values:

```shell script
Generate Faker Content
======================

Number of nested_levels [1]:
Number of visible_levels [1]:
Number of items_per_level [100]:
Number of max_items [1000]:
Number of min_parts [5]:
Number of max_parts [20]:
Number of min_images [0]:
Number of max_images [3]:
Number of location [page://]:
```

Alternatively, you can set **any** or **all** the options via the CLI command itself:

```shell script
$ bin/plugin faker generate --nested-levels=3 --visible-levels=2 --items-per-level=10 --min-parts=5 --max-items=10000 --max-parts=20 --min-images=2 --max-images=5 --location=page://04.faker-pages
```

