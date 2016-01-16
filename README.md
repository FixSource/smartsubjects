# Smart Subjects extension for phpBB

With this extension, editing the subject of the first post in a topic will update all subsequent posts with matching (Re:) subjects to the new subject.

[![Build Status](https://travis-ci.org/VSEphpbb/smartsubjects.png)](https://travis-ci.org/VSEphpbb/smartsubjects)

## Features
* Renaming the first post's subject will update all __matching__ replies to the new title
* Board administrators have the option when editing a first post to force __all__ reply subjects to be updated to match the original post's subject
* Forum based permissions allow you to control which forums and users can use Smart Subjects

## Requirements
* phpBB 3.1.0 or higher
* PHP 5.3.3 or higher

## Installation
1. Download and unzip the [latest release](https://github.com/VSEphpbb/smartsubjects/releases) and copy it to the `ext` directory of your phpBB board.
2. Navigate in the ACP to `Customise -> Manage extensions`.
3. Look for `Smart Subjects` under the Disabled Extensions list, and click its `Enable` link.

## Usage
* Forum based permissions can be configured to disable Smart Subjects in certain forums, or for certain users and usergroups in each forum. They can be found in `Forum Based Permissions -> Forum Permissions` under the `Post` group.

## Uninstallation
1. Navigate in the ACP to `Customise -> Manage extensions`.
2. Click the `Disable` link for Smart Subjects.
3. To permanently uninstall, click `Delete Data`, then delete the `Smart Subjects` folder from `phpBB/ext/vse/`.

## License
[GNU General Public License v2](license.txt)

© 2015 - Matt Friedman (VSE)
