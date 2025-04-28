# WP Markdown Exporter

## Description
The **WP Markdown Exporter** WordPress plugin allows you to export posts as Markdown files based on specific filters such as tags, categories, and date ranges. The exported files are bundled into a ZIP archive that can be downloaded directly.

This plugin uses the [Parsedown](https://parsedown.org/) library to convert post content into Markdown format. It is a useful tool for those who want to back up their posts in Markdown or migrate content to other platforms that support Markdown.

---

## Features
- Export posts based on the following filters:
    - Tag
    - Category
    - Date range
- Automatically generates a ZIP archive containing:
    - Markdown files for each post.
    - An `index.md` file with links to all exported posts for easy navigation.
- Options to organize post files into folders based on their categories.
- Customizable ZIP filename.

---

## Requirements
- **WordPress Version**: 5.0 or higher.
- **PHP Version**: 7.4 or higher (PHP 8.0 recommended).
- **Dependencies**: Parsedown (managed via Composer).

---

## Installation

1. **Download and Install the Plugin**
    - Clone or download the plugin ZIP archive into your WordPress `wp-content/plugins` directory.

2. **Install Dependencies**
    - Navigate to the plugin directory using the terminal and run:
```textmate
composer install
```

3. **Activate the Plugin**
    - Go to the WordPress admin interface.
    - Navigate to `Plugins` → `Installed Plugins`.
    - Activate **WP Markdown Exporter**.

---

## Usage
After activating the plugin, follow these steps to export posts:

1. **Access the Exporter**
    - Navigate to **Markdown Exporter** in the WordPress admin menu.

2. **Set Export Filters**
    - Select a tag, category, or provide a date range to filter posts.
    - You can also specify a custom name for the ZIP file (optional).

3. **Generate ZIP**
    - Click **Generate ZIP**.
    - If no posts match the selected filters, you’ll see an error message: *No posts found for the selected criteria.*

4. **Download the ZIP**
    - The ZIP archive is automatically downloaded to your device. It contains the Markdown files for all exported posts and an `index.md` file.

---

## Export Directory Structure

The ZIP file organizes posts as follows:

```
<custom_file.zip>
|-- content/
|   |-- category-slug/
|       |-- post-name.md
|   |-- uncategorized/
|       |-- post-name.md
|-- index.md (links to all exported posts)
```

- Each category has its own folder.
- Files are named using their post slugs.

---

## Error Handling
### "Please Install Dependencies"
If you see the message:
```
WP Markdown Exporter: please run composer install in the plugin directory to install dependencies.
```
This means the required dependencies (e.g., Parsedown) are missing. Run `composer install` to resolve this.

---

## Troubleshooting
- Ensure you’re using a compatible PHP version (check **Requirements** section).
- Make sure to install required dependencies using Composer.
- If you're unable to download the ZIP file, inspect server permissions for temporary files and folder access.

---

## Contributing
Contributions are welcome! To contribute:
1. Fork the repository.
2. Create a feature branch.
3. Submit a pull request with a detailed description of your changes.

---

## License
This plugin is licensed under the [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html).