**smCore repository**

---------------------------------------------------------------------

This repository is the official development repository of smCore project, a Simple Machines project designed to be the core framework upon which SMF 3.0 is being built. The library will be included in SMF 3.0.

This software is being developed with an Open Development model, driven by the SMF community and contributors.

SMF 3.0 will also be developed with an Open Development model, using both Github tools and home-installed tools. The official Github repository will be found here: https://github.com/SimpleMachines/Simple-Machines-Forum

Goals:
The smCore project will be working to develop a platform for development of future versions of SMF, allowing easy interaction with other large modules such as a blog, portal or CMS, gallery, and other large and desirable parts, of a community-based website.

Currently, many of these things are hacked onto the forum and live as second-class citizens on your website. But SMF developers had always envisioned a set of website "simple machines" to do most of the hard work of the forum and all the other essential pieces of your community-based website.

What is smCore?
smCore will be a "core" of reusable code that will provide the basis for the development of future versions of SMF.

What smCore is not
smCore will not do anything all on its own, but provide the core functions upon which SMF functionalities are being built.

For more information about SMF future, please refer to the development blog: http://www.simplemachines.org/community/index.php?topic=469381.0

**How to Contribute**

Code contributions are always welcome. We're developing using the fork and pull request model as per GitHub practice. Please check out: [Pull requests](http://help.github.com/send-pull-requests)
* Fork the repository, and clone it
* Commit, as many times as you need to achieve the desired result
* Sign-off your commits, to certify the changes are under the license of the project, as per: [Developer Certificate of Origin](https://github.com/SimpleMachines/smCore/blob/master/DCO.txt)
* Push your changes to your repository, ideally to a branch, dedicated to the issue
* Create a pull request to the main repository

Most of the actual work will be done in personal repositories, before being hashed out and merged in the official repository.

If you have features or ideas to propose, fill an issue here: [Open Issues](https://github.com/SimpleMachines/smCore/issues)


Please feel free to check out related repositories:
* https://github.com/norv/SMF3.0
* https://github.com/Fustrate/smCore
* https://github.com/Fustrate/Twig

Go ahead and fork this project now. We're glad to have you.

-----------------------------------------------------------------------

Installation requirements:
- PHP 5.3+
- Database support: currently tested on MySQL, with MySQLi or PDO. Character set: UTF8. The target is to support multiple database systems, among which PostgreSQL 9+.

Instructions:
- The file core_tables.sql from the main directory contains the queries to be executed for installation. Todo: add the install script, and migration from SMF 2.0.

- The file Settings.php from the /other directory contains the settings for the installation. Customize it as needed, and copy it on your setup in the main directory.

More details:
In the main directory there is a file Database changes from SMF, being... what it says it is, surprisingly. ;) The file 'specs' is just a quick todo file, to be removed and replaced with proper specifications, documented for the respective modules (it's not exactly smCore itself).

------------------------------------------------------------------------------------

License:
Version: MPL 1.1

The contents of this package are subject to the Mozilla Public License Version 1.1 (the "License"); you may not use this package except in compliance with the License. You may obtain a copy of the License at http://www.mozilla.org/MPL/

Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for the specific language governing rights and limitations under the License.

The Original Code is smCore.

The Initial Developer of the Original Code is the smCore project.

Portions created by the Initial Developer are Copyright (C) 2011 the Initial Developer. All Rights Reserved.

Contributor(s): Compuart, SleePy

Alternatively, the contents of this package may be used under the terms of another license, specified beforehand by the Initial Developer. 

-----------------------------------------------------------------------------

smCore third-party libraries. These libraries terms are specified by their own licenses.
(please see the LICENSE file in their respective directory)

Submodules:
library/php-code-coverage
git://github.com/sebastianbergmann/php-code-coverage

library/php-file-iterator
git://github.com/sebastianbergmann/php-file-iterator

library/php-text-template
git://github.com/sebastianbergmann/php-text-template

library/phpunit
git://github.com/sebastianbergmann/phpunit

To initialize submodules (first checkout), run
git pull origin master
git submodule init
To update submodules (fetch code), run
git submodule update

Included libraries at this time:
* library/smTE
* library/Inspekt
* library/sfYaml
