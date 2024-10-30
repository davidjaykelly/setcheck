# 🛠️ **Moodle Plugin: Setcheck**

## Overview 📋

The **Setcheck** plugin for Moodle simplifies the setup of activity templates, allowing administrators and users to apply predefined settings across multiple activities, saving time and reducing errors. Templates are context-aware, meaning they’re only available in the categories, subcategories, and courses where they were created—keeping configurations consistent in relevant areas.

## Features ✨

- **🗂 Contextual Templates**: Create templates for Moodle activities (currently assignments, with plans to expand to other activity types) that are only available in the context (category, subcategory, or course) in which they were created.
- **🚠 Activity Template Management**: View, edit, and manage templates directly from **More > Manage Activity Templates**, accessible under categories and courses.
- **🔒 Role-Based Permissions**: Configure permissions based on roles, allowing specific roles to create templates within their assigned courses or categories.

### 🚀 Planned Enhancements

- **🌐 Multi-Activity Support**: Extend templates to multiple activity types, potentially across all Moodle activities.
- **⚙️ Site-Wide Template Management**: Enable administrators to globally manage templates, with capabilities to view, edit, and delete any template.
- **🔒 Advanced Role Permissions**: Refine permissions to allow roles specialized access (e.g., course-level template creation only, restricted editing based on role).

---

## Installation ⚙️

1. Clone the repository or download the plugin zip file:
   ```bash
   git clone <repository-url>
   ```
2. Place the `setcheck` directory into your Moodle instance's `local` directory:
   ```
   /path/to/moodle/local/setcheck
   ```
3. Visit **Site administration** in Moodle to complete the installation.

---

## Requirements 📌

- **Moodle** version 4.0 or higher
- **PHP** version 7.4 or higher

---

## Usage 💡

1. Go to a course or category in Moodle.
2. Select **More > Manage Activity Templates**.
3. From this page, you can create, edit, and manage templates. Templates are specific to the context (category or course) where they are created.
4. Use the templates when setting up activities to apply predefined configurations quickly.

---

## Development Setup 🛠️

To set up a development environment, follow these steps:

1. Ensure you have [VS Code](https://code.visualstudio.com/) with the necessary PHP extensions for Moodle development.
2. Clone this repository and open it in VS Code.
3. Use Moodle’s PHP built-in server to run your development site:
   ```bash
   php -S localhost:8000
   ```

### Branching Strategy 🌱

- **`master`**: The current working codebase
- **`dev`**: For integration and ongoing development work
- **Feature branches**: Created from `dev` for specific features or bug fixes; merged back into `dev` once tested

#### Creating a Feature Branch 🌿

1. Start by creating a new feature branch from `dev`:
   ```bash
   git checkout dev
   git pull origin dev
   git checkout -b feature/new-feature
   ```
2. Once development is complete, push the feature branch:
   ```bash
   git push -u origin feature/new-feature
   ```

---

## Contributing 🤝

1. **Fork** the repository.
2. **Create** a feature branch (`git checkout -b feature/your-feature-name`).
3. **Commit** your changes (`git commit -m 'Add a new feature'`).
4. **Push** to the branch (`git push origin feature/your-feature-name`).
5. Open a **pull request**.

---

## Troubleshooting 🧐

If you experience issues:

- Ensure you have the required Moodle and PHP versions.
- Run `php -S localhost:8000` to check if the development server is working.
- Refresh your local branches to stay in sync with GitHub:
  ```bash
  git fetch -p
  git branch -d dev
  ```

---

## License 📜

Licensed under the [GNU General Public License v3](https://www.gnu.org/licenses/gpl-3.0.html).

---

## Contact 🛩️

For questions or support, contact [David Kelly](mailto:davidjaykelly@gmail.com).
