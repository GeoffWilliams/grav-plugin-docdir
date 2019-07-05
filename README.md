# Docdir Plugin

The **Docdir** Plugin is an extension for 
[Grav CMS](http://github.com/getgrav/grav) to let you publish directories of 
documents inside your site.

Your documentation should be laid out as follows:

```
user/pages/02.products/99.anything_you_like
├── 1_4_0
│   ├── 01.intro
│   │   └── doc.md
│   ├── 02.setup
│   │   └── doc.md
│   ├── 03.troubleshooting
│   │   └── docdir.md
│   ├── docdir.md
│   └── images
├── 2_0_0
│   ├── 01.basics
│   │   └── docdir.md
│   ├── 02.setup
│   │   └── docdir.md
│   ├── 03.usage
│   │   └── docdir.md
│   ├── 04.troubleshooting
│   │   └── docdir.md
│   ├── docdir.md
│   └── images
└── docdir.md

```

This gives you a drop-down to switch documentation version, separate docs and 
images for each version and a stable URI as well. The most recent version is
shown as the default and this is selected by reverse sorting the version
directories

**Important**
To avoid searching files, you must specify the top level directory in each 
`docdir.md` and `doc.md` files in the frontmatter, eg:

```yaml
docdir: /products/anything_you_like
``` 

## Installation

Installing the Docdir plugin can be done in one of three ways: The GPM (Grav Package Manager) installation method lets you quickly install the plugin with a simple terminal command, the manual method lets you do so via a zip file, and the admin method lets you do so via the Admin Plugin.

### GPM Installation (Preferred)

To install the plugin via the [GPM](http://learn.getgrav.org/advanced/grav-gpm), through your system's terminal (also called the command line), navigate to the root of your Grav-installation, and enter:

    bin/gpm install docdir

This will install the Docdir plugin into your `/user/plugins`-directory within Grav. Its files can be found under `/your/site/grav/user/plugins/docdir`.

### Manual Installation

To install the plugin manually, download the zip-version of this repository and unzip it under `/your/site/grav/user/plugins`. Then rename the folder to `docdir`. You can find these files on [GitHub](https://github.com/geoff-williams/grav-plugin-docdir) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/docdir
	
> NOTE: This plugin is a modular component for Grav which may require other plugins to operate, please see its [blueprints.yaml-file on GitHub](https://github.com/geoff-williams/grav-plugin-docdir/blob/master/blueprints.yaml).

### Admin Plugin

If you use the Admin Plugin, you can install the plugin directly by browsing the `Plugins`-menu and clicking on the `Add` button.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/docdir/docdir.yaml` to `user/config/plugins/docdir.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
```

Note that if you use the Admin Plugin, a file with your configuration named docdir.yaml will be saved in the `user/config/plugins/`-folder once the configuration is saved in the Admin.

## Usage

**Describe how to use the plugin.**

## Credits

**Thanks to the [grav-breadcrumbs-plugin](https://github.com/getgrav/grav-plugin-breadcrumbs)**

## To Do


