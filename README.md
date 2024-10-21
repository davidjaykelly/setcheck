# Moodle Plugin: Setcheck

## Overview

The Setcheck plugin for Moodle enhances the assignment setup process by providing assignment templates that save time and reduce errors. Administrators can predefine assignment settings, allowing users to select a template and apply consistent configurations across multiple assignments.

## Features

- **Assignment Templates**: Create assignment templates to save time on repetitive tasks.
- **Template Management**: View, edit, and manage templates from the management page.

## Installation

1. Clone the repository or download the plugin zip file.
   ```bash
   git clone <repository-url>
   ```
2. Place the `setcheck` directory into your Moodle instance's `local` directory.
   ```
   /path/to/moodle/local/setcheck
   ```
3. Visit the **Site administration** page in Moodle to complete the installation.

## Requirements

- Moodle version 4.0 or higher.
- PHP version 7.4 or higher.

## Usage

1. Navigate to **Site administration > Plugins > Setcheck**.
2. Click on **Create Template** to add a new template.
3. Define the assignment settings and save the template.
4. Use the templates when creating new assignments in Moodle courses to quickly set the required configurations.

## Development Setup

For development purposes, you can use the following steps:

1. Ensure you have set up [VS Code](https://code.visualstudio.com/) with the necessary PHP extensions for Moodle development.
2. Clone this repository and open the project in VS Code.
3. Use the Moodle PHP built-in server to run your development site:
   ```bash
   php -S localhost:8000
   ```

### Branching Strategy

- **`master` branch**: The current working codebase.
- **`dev` branch**: For integration and ongoing development work.
- **Feature branches**: Created from `dev` to add features or bug fixes. Once tested, they are merged back into `dev`.

#### Creating a Feature Branch

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

## Contributing

1. Fork the repository.
2. Create a feature branch (`git checkout -b feature/your-feature-name`).
3. Commit your changes (`git commit -m 'Add a new feature'`).
4. Push to the branch (`git push origin feature/your-feature-name`).
5. Open a pull request.

## Troubleshooting

If you experience issues with the plugin:

- Ensure you have the required Moodle and PHP versions.
- Run `php -S localhost:8000` to check if the development server is working as expected.
- Delete the `dev` branch locally and pull the latest branches from GitHub to ensure your environment is in sync:
  ```bash
  git fetch -p
  git branch -d dev
  ```

## License

This project is licensed under the [GNU General Public License v3](https://www.gnu.org/licenses/gpl-3.0.html).

## Contact

For questions or support, contact [David Kelly](mailto:davidjaykelly@gmail.com).
