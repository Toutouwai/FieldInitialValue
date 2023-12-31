# Field Initial Value

For most field types, allows the definition of an initial value that is automatically set when pages are created.

The initial value can be set in template context if you want a different initial value in different templates.

Example for a "Countries" Page Reference field using AsmSelect:

![initial-value-1](https://github.com/Toutouwai/FieldInitialValue/assets/1538852/40f23798-f9ab-44cd-b79c-b1572902dde9)

Example with explanatory notes in a CKEditor field:

![initial-value-2](https://github.com/Toutouwai/FieldInitialValue/assets/1538852/bfae62e8-76e4-491b-a5fd-b78aa7c3b124)

## Differences from "Default value"

The core allows setting a "Default value" for certain field types. The "Initial value" setting added by this module is different from the "Default value" setting in the following ways:

1. The "Default value" is a setting that applies only to the *inputfield*, meaning that the value is shown in the inputfield by default but is not stored until you save the page. By contrast, the "Initial value" is automatically saved to the page when the page is first created.
2. Related to point 1, when a page is created via the API rather than in the ProcessWire admin the "Initial value" is saved to the page but the "Default value" is not.
3. The "Default value" has no effect when a field is not "required", but the "Initial value" is set for both required and not required fields.
4. Related to point 3, a user can empty a field that had an "Initial value" applied but a field with a "Default value" set cannot be emptied.
5. The "Initial value" setting is available for more field types than "Default value".

## Supported field types

The following field types are supported, along with any types that extend them:

* FieldtypeText (includes types that extend it such as Textarea, CKEditor, URL, etc.)
* FieldtypeDatetime
* FieldtypeInteger
* FieldtypeDecimal
* FieldtypeFloat
* FieldtypePage (with all core inputfield types)
* FieldtypeCheckbox
* FieldtypeOptions  (with all core inputfield types)
* FieldtypeToggle
* FieldtypeSelector
* FieldtypeMultiplier (ProField)
* FieldtypeCombo (ProField, only supported when File and Image subfields are not used)
* FieldtypeStars (third-party module)

If you want to try field types beyond this you can define additional types in the module config, but your mileage may vary.

## Unsupported field types

It's not possible to set an initial value for these field types, along with any types that extend them:

* FieldtypeFile (and FieldtypeImage)
* FieldtypeRepeater (includes types that extend it such as Repeater Matrix and Fieldset Page)
* FieldtypePageTable
* FieldtypeTable (ProField)

## Notes

Seeing as the initial value is defined in the field config it has no idea of the current page - for the purposes of rendering the initial value inputfield the home page is supplied as a dummy page. This probably isn't an issue in most cases but it might have an effect for some Page Reference fields if they use the current page to limit the selectable pages.