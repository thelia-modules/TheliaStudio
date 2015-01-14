Thelia Studio
===
author: Benjamin Perche <bperche@openstudio.fr>

This module allows you to generate all the repetitive classes and templates you have to write for a module.

1. Installation
---

Install it as a thelia module by downloading the zip archive and extracting it in ```thelia/local/modules``` or by uploading it with the backoffice,
or by requiring it with composer:
```json
"require": {
    "thelia/thelia-studio-module": "~1.0"
}
```

2. Usage
---

This module adds two commands:
```$ php Thelia module:generate:everything```
and
```$ php Thelia module:generate:config-form```

3. Generating your module configuration form
---

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

Here's an example:
```yaml
config:
  var_name: text
  var_name2: integer
  var_name3: checkbox
  var_name4:
    type:  text
    required: false
    regex: "a-z+"
    size:
      min: 5
      max: 20
```

4. How to use it ?
---

### 4.1 Generate the configuration form only

First, write your config-form.yml
Then launch ```$ php Thelia module:generate:config-form```
Finally, adapt the generated template for your need.

### 4.2 Generate table CRUD and configuration form

#### 4.2.1 Writing the schema.xml

If you want your table to be correctly integrated into Thelia, you have to respect some conventions.

- Always call your primary key ```id``` and never construct the PK with two columns.
- If you want a visibility toggle, call your column ```visible``` and give it the type ```BOOLEAN``` or ```TINYINT```
- If you want a position management, call your column ```position```. The order argument for the loop will be called manual
- If the column is called 'id', 'title', 'name' or 'code' its entries in the table will be a clickable link
- If the column is a ```BOOLEAN``` or a ```TINYINT``` type, it will be used as a checkbox.
- If the column is called 'chapo', 'description' or 'postscriptum', it won't be displayed in the table

#### 4.2.2 Generating everything

Write your schema.xml, then if needed write your config-form.yml
You can now launch ```$ php Thelia module:generate:everything```, your can use the --table option to specify the tables you want to generate.

Go to the ```Form``` directory and change the form names that you want.
You can change the generated templates as you want, as the generator integrates everything, everywhere, even if it's not needed.

5. Access to the generated pages
---

The CRUDs are generated under ```/admin/module/ModuleCode/table_name```
You can write a hook to access it from the ```Tools``` dropdown or add links into your module configuration page.