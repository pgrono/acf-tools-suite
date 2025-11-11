# ACF Tools Suite

![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)
![WordPress Tested Up To](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)
![Requires ACF](https://img.shields.io/badge/Requires-ACF%205.0%2B-orange.svg)

A developer's toolkit for Advanced Custom Fields designed to accelerate your workflow. Features an intelligent snippet generator that creates complete loops for complex fields, and a powerful debugger to inspect live field values.

## Overview

ACF Tools Suite is designed to eliminate repetitive tasks and provide clarity when working with Advanced Custom Fields. It provides two essential, time-saving utilities right in your WordPress dashboard, helping you write better code faster and debug with ease.

Stop writing the same boilerplate code over and over again, and get a crystal-clear view of your field data without ever needing to use `var_dump()`.

## 1. Intelligent Code Generator

Instantly generate clean, ready-to-use PHP snippets for any of your ACF fields. The generator is smart and saves you valuable development time by understanding your field structures.

*   **Automatic Loop Generation:** It automatically detects complex fields like **Repeaters**, **Flexible Content**, **Galleries**, and **Relationship** fields, generating the complete loops for you.
*   **Sub-Field Awareness:** The generator inspects your Repeater and Flexible Content layouts and automatically includes `get_sub_field()` calls for all of your *actual* sub-fields, complete with variable assignments. No more guesswork or typos.
*   **Options Page Support:** Seamlessly generates code for fields located on an ACF Options Page, including the required `'option'` parameter.
*   **Simple & Complex Snippets:** Provides quick one-liners for simple fields (`get_field()`) and complete, context-aware loops for array-based fields.
*   **Plugin Integration:** Detects if the "Orphans" plugin is active and provides extra code snippets using its filter.

## 2. Powerful Field Debugger

A clean and efficient way to inspect the raw data stored in your ACF fields across your entire site. It's the perfect tool for troubleshooting and development.

*   **Clutter-Free View:** The debugger is smart—it only displays posts that have **non-empty** ACF fields, so you only see the data that matters.
*   **Organized by Post Type:** Field data is neatly grouped by post type and then by individual post or page for easy navigation.
*   **Quick Edit Links:** Every entry includes a direct link to the post's edit screen for immediate access.
*   **Options Page Debugging:** Easily view a dump of all non-empty fields saved to your Options Page in its own dedicated section.

## Installation

1.  Download the latest release from the [Releases](https://github.com/pgrono/acf-tools-suite/releases) page as a `.zip` file.
2.  In your WordPress dashboard, navigate to **Plugins > Add New**.
3.  Click **Upload Plugin** and select the `.zip` file you downloaded.
4.  Activate the plugin.

Alternatively, you can clone this repository directly into your `wp-content/plugins` directory.

## How to Use

Once activated, you will find a new menu item in your WordPress admin sidebar called **"ACF Tools"**.

*   **ACF Tools > Code Generator:** This is where you can find and copy your code snippets. The page is automatically populated with all your existing field groups.
*   **ACF Tools > Field Debugger:** Use this page to inspect live field values from your posts, pages, custom post types, and the ACF options page.

## Contributing

Found a bug, have an idea for a new feature, or want to improve translations? Contributions are welcome! Please feel free to open an issue or submit a pull request.

## License

This plugin is licensed under the GPL v2 or later. See the `license.txt` file for more details.
