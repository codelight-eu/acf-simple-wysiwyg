# Advanced Custom Fields: Simple Wysiwyg field
WordPress plugin that creates a new ACF Wysiwyg field type with very limited controls - bold, italic, link and colors. Perfect for headings.

## How it looks like
![Screenshot 1](https://imgur.com/xZ9m8vv.png)

## Why?
Sometimes you need a page heading with a fixed h1 tag, but with _some_ styling - for example, a word that's bold or underlined or even a link.
This field type allows doing that, but doesn't add paragraph tags and removes all unnecessary controls so your clients won't accidentally screw up the layout.

Shortcodes will still work, though.

## Installation
`composer require codelight-eu/acf-simple-wysiwyg`

## Where can I find the new field type
![Screenshot 1](https://imgur.com/XzOpFjl.png)

## Can I change the tinyMCE controls?
Sure. Use the following filter: `acf/fields/simple-wysiwyg/toolbars`
Looks like this in the code:

```php
<?php
$toolbars = apply_filters('acf/fields/simple-wysiwyg/toolbars', [
    'Full' => [
        1 => apply_filters('mce_buttons', [
            'bold',
            'italic',
            'underline',
            'link',
            'unlink',
            'forecolor',
            'forecolorpicker',
            'pastetext',
            'removeformat',
        ], 'acf_content'),
    ]
]);
```
