# Elementor Forms – WYSIWYG Field

# Elementor Forms – WYSIWYG Field

Adds a rich-text (TinyMCE) WYSIWYG field type to Elementor Pro forms.

## Features

- Integrates the TinyMCE editor into the Elementor Pro Form widget.
- Sanitises user input with `wp_kses_post()`.
- Supports dynamically loaded forms (pop-ups, tabs).
- Lightweight: loads assets only on pages where the form appears.

## Installation

1. Upload the `elementor-form-wysiwyg-field` folder to `wp-content/plugins/`.
2. Activate **Elementor Forms – WYSIWYG Field** in **Plugins**.
3. Ensure **Elementor Pro** is installed and active.

## Usage

1. In Elementor, add or edit a **Form** widget.
2. Click **Add Item**, then choose **WYSIWYG** under the **Type** dropdown.
3. Configure the field label, placeholder, and required status as needed.
4. (Optional) Add **Collect Submissions** under **Actions After Submit** to save submissions.

## Developer Notes

- Core TinyMCE assets are enqueued via `wp_enqueue_editor()`.
- The initialiser script (`assets/wysiwyg-field.js`) hooks into `frontend/element_ready/form.default` and on initial page load.
- Sanitisation of submitted HTML is handled in `form-fields/wysiwyg.php` via `wp_kses_post()`.

## License

GPL-2.0-or-later