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
