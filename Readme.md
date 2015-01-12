Thelia Studio
===
author: Benjamin Perche <bperche@openstudio.fr>

This module allows you to generate all the repetitive classes and templates you have to write for a module.

1. Installation
Install it as a thelia module, that module by downloading the zip archive and extracting it in ```thelia/local/modules```, by uploading it with the backoffice
and by requiring it with composer:
```json
"require": {
    "thelia/thelia-studio-module": "~1.0"
}
```

2. Usage

This module adds two commands:
```$ php Thelia module:generate:everything```
and
```$ php Thelia module:generate:config-form```

3. Generating your module configuration form

To do that, you will need to write a new file.
Create ```config-form.yml``` in your module's Config directory,
and by respecting the following structure, TheliaStudio will generate everything for you :) :
- the root node is called 'config'
- then write your config ```varName: type``` for simple ones.
    - available types are: text, textarea, integer, number, checkbox.
    - you can add more precise data into the generation:
        - ```required: false``` if the field isn't required
        - ```regex``` a validation regex.
        - ```size``` an array with "min" and "max" keys.

You have an example in Tests/fixtures/config-form.yml