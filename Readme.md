### Gendiff
Gendiff is a utility for comparing two configuration files.
Supported formats: json, yaml.
The result of comparing can be displayed in different formats: for example, plain or json.


[![Maintainability](https://api.codeclimate.com/v1/badges/98ea2f7eb7f6a4613086/maintainability)](https://codeclimate.com/github/rentery/php-project-lvl2/maintainability)

[![Test Coverage](https://api.codeclimate.com/v1/badges/98ea2f7eb7f6a4613086/test_coverage)](https://codeclimate.com/github/rentery/php-project-lvl2/test_coverage)

![PHP CI](https://github.com/rentery/php-project-lvl2/workflows/PHP%20CI/badge.svg)

## How to install
1. Installation with composer as a cli utility:

Enter the command `composer global require dekabruga/php-project-lvl1` to install Brain-games

2. You also can use this utility as library:

Enter the command in your project `composer require dekabruga/php-project-lvl1` to install 

and import function `use Differ/Differ/genDiff;`

## How it works

You can compare two json config files with plain structure

[![asciicast](https://asciinema.org/a/Gg31KUvyUZ3E8QEie5pJcm34o.svg)](https://asciinema.org/a/Gg31KUvyUZ3E8QEie5pJcm34o)

You can compare two yaml config files with plain structure

[![asciicast](https://asciinema.org/a/t1wAlCN93XEDsdnN1l2wg2giy.svg)](https://asciinema.org/a/t1wAlCN93XEDsdnN1l2wg2giy)

You can compare two yaml or json config files with nested structure

[![asciicast](https://asciinema.org/a/oyRa2QjXCJ0hH1sG1pXUWOm4T.svg)](https://asciinema.org/a/oyRa2QjXCJ0hH1sG1pXUWOm4T)


You can use `--format plain` for plain data output

[![asciicast](https://asciinema.org/a/ed8rlz4LWjWohBL01erkgil6E.svg)](https://asciinema.org/a/ed8rlz4LWjWohBL01erkgil6E)

Finally, you can use `--format json` for json data output

 https://asciinema.org/a/poYvlw8btt4GONSHUdhfJ0wQp json