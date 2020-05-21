# Chenglish Dictionary

An app for Chinese language learners. Includes a massive Chinese-English dictionary powered by [CC-CEDICT](http://cc-cedict.org/) with the additional function of creating, saving, producing, and managing highly functional vocabulary lists for personal use and study. [Read more](http://mattberti.squarehaven.com/work/chenglishdict/) about this app and why I made it.

## Built With

* PHP 7
* Javascript and jQuery

## Launch

Dependencies listed in [composer.json](composer.json); Set up your environment: `composer i`

## Tests

Tests can be run with [PHPUnit](https://phpunit.de/) within the `tests` folder. A database facsimile on your local machine is required for most tests.

## Contributing

Contributions welcome!

## Authors

* **Matt Berti** - *Initial work* - [Dr Spaceman](https://github.com/dr-spaceman)
* Your name here

## Acknowledgments

* Dictionary data provided by [CC-CEDICT](http://cc-cedict.org/)
* [eric okorie's PrimezeroTools](http://code.google.com/p/pzphp/wiki/PrimezeroTools) for pinyin and romanization 

## Todo

* Back end
    * [x] ~Convert mysql to pdo~
    * [x] ~Classes to manage db tables and app functions~
        * [x] ~User class~
        * [x] ~Vocab class~
        * [x] ~Zhongwen class~
    * [x] ~Guest user can begin vocab list without registering~
    * [ ] REST API
* Front end
    * [x] ~Responsive mobile-first CSS~
    * [ ] Flashcards swipe for mobile
