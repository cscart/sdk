# CS-Cart SDK
CS-Cart & Multivendor Command Line Tools for Developers Pride.

## Purposes
We want to provide convenient developer tools for CS-Cart & Multi-Vendor. This SDK is meant to handle complicated and routine tasks related to add-on and theme development.

## Usage
### Installing
You'll need [Composer](https://getcomposer.org) installed in your system. Check out its [installation guide](https://getcomposer.org/doc/00-intro.md#globally) if you haven't done that before.

When the Composer is installed, just execute this command in your console:
```bash
$ composer global require "cscart/sdk:*"
```

### Executing commands

```bash
$ cscart-sdk command:name
```

### Command list

##### addon:symlink
Creates symlinks for add-on files at the CS-Cart installation directory, allowing you to develop and store add-on files in a separate Git repository.

```
$ cscart-sdk addon:symlink --help
Usage:
  addon:symlink [options] [--] <name> <addon-directory> <cart-directory>

Arguments:
  name                       Add-on ID (name)
  addon-directory            Path to directory with add-on files
  cart-directory             Path to CS-Cart installation directory

Options:
  -r, --relative             Created symlinks will have a relative path to the target file. By default the created symlinks have an absolute path to target.
      --templates-to-design  Whether to take the add-on templates from "var/themes_repository" path at the add-on directory and put them at "design/themes" path in the CS-Cart installation directory . When this option is not specified, the templates are being taken from "var/themes_repository" and also put into "var/themes_repository" directory.
  -h, --help                 Display this help message
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi                 Force ANSI output
      --no-ansi              Disable ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
 Creates symlinks for add-on files at the CS-Cart installation directory, allowing you to develop and store add-on files in a separate Git repository.
```

##### addon:export
Copies or moves all add-on files to the separate directory, preserving the structure of directories.

```
$ cscart-sdk addon:export --help
Usage:
  addon:export [options] [--] <name> <addon-directory> <cart-directory>

Arguments:
  name                         Add-on ID (name)
  addon-directory              Path to directory where files should be moved to
  cart-directory               Path to CS-Cart installation directory

Options:
  -d, --delete                 Files and directories will be moved instead of being copied.
      --templates-from-design  Whether to take the add-on templates from "design/themes" path at CS-Cart installation directory and put them at "var/themes_repository" path in the add-on files directory. When this option is not specified, the templates are being taken from "var/themes_repository" and also put into "var/themes_repository" directory.
  -h, --help                   Display this help message
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi                   Force ANSI output
      --no-ansi                Disable ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
 Copies or moves all add-on files to the separate directory, preserving the structure of directories.
```

##### addon:sync
Synchronizes add-on files between CS-Cart installation directory and the separate directory storing all add-on files. Calling this command has the same effect as calling the "addon:export" and "addon:symlink" commands simultaneously.

```
$ cscart-sdk addon:sync --help
Usage:
  addon:sync [options] [--] <name> <addon-directory> <cart-directory>

Arguments:
  name                  Add-on ID (name)
  addon-directory       Path to directory where files should be moved to
  cart-directory        Path to CS-Cart installation directory

Options:
  -r, --relative        Created symlinks will have a relative path to the target file. By default the created symlinks have an absolute path to target.
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
 Synchronizes add-on files between CS-Cart installation directory and the separate directory storing all add-on files. Calling this command has the same effect as calling the "addon:export" and "addon:symlink" commands simultaneously.
```

## Contributing
To contribute to this project, you need to know how to work with Git and GitHub:

1. If you haven’t used Git before, check out [this tutorial](http://try.github.io/); you can also read [Git documentation](https://git-scm.com/documentation) or find other tutorials on the Internet;
2. If you want to learn more about GitHub, check out [GitHub Help](https://help.github.com/).

You’ll need a GitHub account to submit an issue or a pull request.

#### Submitting an Issue
Before you submit an issue, please run a search to see that it wasn’t submitted before. That way we’ll be able to deal with issues faster.

If the issue appears to be a bug, and it hasn’t been reported yet, open a new issue: [switch to the Issues tab](https://github.com/cscart/sdk/issues), press the **New Issue** button and fill in the form. You’ll need to be logged in to your GitHub account.

When submitting an issue, please provide the following information, so that we can fix it quickly:

* **Short summary of the issue** - That helps us to keep things organized.

* **Why is it a problem for you?** - Not all issues are bugs. If you have a suggestion on how to improve SDK, please tell us how this improvement would benefit the project.

* **Browsers and operating systems** - If we know that the issue appears only in specific browsers or only in some operating systems, we’ll be able to reproduce it faster.

* **Steps to reproduce the issue** - We need to see the issue for ourselves to confirm and fix it.

* **Suggest a fix** - If you know what might be causing the bug, please let us know.

#### Submitting a Pull Request

GitHub allows you to make a full copy of the SDK and work on it separately. Once you’ve made some changes, you can send us a pull request so that we can include your changes to the main repository.

To contribute to SDK development, do this:

1. [Register an account at GitHub](https://github.com/join), if you haven’t done it yet—you’ll need the account to complete the following steps.
2. [Fork](https://help.github.com/articles/fork-a-repo/) the SDK — get your own copy of the main SDK repository to work on and experiment with.
3. [Clone your fork](https://help.github.com/articles/cloning-a-repository/) to your local machine—a local repository is where all the work is done.
4. [Create a branch](https://git-scm.com/book/en/v2/Git-Branching-Basic-Branching-and-Merging) in your local clone—having separate branches for different tasks helps to keep things organized.
5. Work on the SDK in this branch. Please make sure to follow PSR coding standards.
6. [Push your changed branch](https://help.github.com/articles/pushing-to-a-remote/) to your fork in your GitHub account—the changes you made locally will appear in your online repository.
7. [Create a pull request](https://help.github.com/articles/using-pull-requests)—submit your changes to us.

That’s it! Our specialists will review the changes and may pull them to the repository.

#### Preparing local development environment

After cloning the forked repository, you'll want to be able to run the `cscart-sdk` command to test things locally.
In order to do that, you'll need to install the Composer package from local path.

Add these lines to your global composer configuration file located at `~/.composer/composer.json` path:
```json
{
    "minimum-stability": "dev",
    "repositories": [
        {
            "type": "path",
            "url": "/path/to/cloned/repository/directory"
        }
    ],
    "require": {
        "cscart/sdk": "*"
    }
}
```
Don't forget to specify path to the correct directory where you cloned your fork of a repo.

After that, execute this command:

```sh
$ composer global require "cscart/sdk:*"
```

You need to do this only once; there is no need to re-install the local package every time you make a change in code. Directory with forked repository will be symlinked to your globally installed Composer packages directory.

You're now can test your changes by executing globally available `cscart-sdk` command.

## Copyright and License
Code released under the [MIT license](https://opensource.org/licenses/MIT).
