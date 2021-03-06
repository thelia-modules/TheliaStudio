1.4.11
---
- Fix model class generation (conflict with Propel model generation)

1.4.10
---
- Event dispatcher is now injected int the model.

1.4.9
---
- Added a data transformer to the text and textarea form fields to allow the storage of an empty field in the database when a value has been previously defined.

1.4.8
---
- "url" column declaration no longer required for automatic SEO management

1.4.7
---
- This release fixes a typo in composer.json

1.4.6
---
- Added automatic standard SEO support to the module. This support is enabled on a table if this table has all the following columns :
  - meta_title
  - meta_description
  - meta_keywords

- "model" files are no longer overwritten if they already exist.


1.4.4
---
- Thelia 2.3 compatibility

1.4.x
---
- Add fields label and help for config form item
- Add possibility to translate config form field

1.4.1
---
- Fix double class in same namespace ( Event/Base )

1.4
---
- Fix timestampable loop parameter generation
- Add full module events class
- Add filter on ```module:generate:all``` command to only generate for some directories
- Fix module config form generation
- Only generate the module class if the ```MESSAGE_DOMAIN``` is not present in the class

1.3
---
- Fix datetime picker generation
- Add ModelEventDistpacherTrait on visible only table
- Do not generate config form elements if the file ```config-form.yml``` is missing or empty

1.2
---
- Fix double quote on create form generation
- Fix empty hook tag in config.xml generation
- Fix header in config form classes.
