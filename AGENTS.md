# Beanstalk Console - Agent Instructions & Guidelines

Welcome! This file contains development guidelines, architectural details, and coding standards for the Beanstalk Console project. All AI agents working on this repository must adhere to these rules.

---

## 1. Project Overview
**Beanstalk Console** is a web-based administration and monitoring console for the [Beanstalkd](https://beanstalkd.github.io/) queue server, written in PHP. 

### Key Features
- Server management (global config + user-added servers via cookies/environment).
- Tube statistics and real-time monitoring.
- Job management (peek, kick, delete, add, move jobs between tubes).
- Sample jobs storage (saving job templates to a local JSON file).
- Job search capability.

---

## 2. Architecture & Directory Structure

The project uses a lightweight, custom MVC-like structure without a heavy framework:

- **`public/index.php`**: The main entry point. Handles Basic Authentication, loads configuration, initializes the `Console` controller, and renders the main template.
- **`config.php`**: Contains default configuration settings. **Do not modify this file for local setup.**
- **`config.local.php`**: (Git-ignored) User-specific local configuration overrides.
- **`lib/include.php`**: The core bootstrap file. Contains the custom class autoloader and the `Console` class, which acts as the main controller handling all actions (e.g., `_actionKick`, `_actionAddjob`, `_actionSearch`).
- **`lib/Pheanstalk/`**: A bundled version of the Pheanstalk library (v2/v1, non-namespaced) used to communicate with the Beanstalkd server.
- **`lib/tpl/`**: Contains PHP template files (e.g., `main.php`, `allTubes.php`, `currentTube.php`) rendered by the `Console` class.
- **`src/`**: Contains the core model and utility classes:
  - `Model.php`: Base model class using magic getters/setters and late static binding.
  - `Server.php`: Represents a Beanstalkd server instance.
  - `Settings.php`: Manages UI and user-specific settings.
  - `Storage.php` / `IStorage.php`: Handles saving and retrieving sample jobs from `storage.json`.
- **`public/assets/`, `public/css/`, `public/js/`**: Static assets, stylesheet (`customer.css`), and JavaScript logic (`customer.js` + jQuery plugins).

---

## 3. Technology Stack & Compatibility

### Backend (PHP)
- **PHP Version Compatibility**: The project requires and supports **`PHP 7.0.0` through `PHP 8.5+`** (declared as `"php": ">=7.0.0"` in `composer.json`).
- **No Namespaces**: Do not use PHP namespaces. All class names must be unique and defined in the global namespace.
- **Syntax Constraints**:
  - **Null Coalescing & Arrays**: Use the null coalescing operator (`??`) and short array syntax (`[]`) freely, as they are fully supported in PHP 7.0+.
  - **Avoid PHP 7.4+ features**: Do not use features like arrow functions `fn()`, typed properties, or null coalescing assignment `??=`.
  - **Avoid PHP 8.0+ features**: Do not use union types, constructor promotion, match expressions, named arguments, or the nullsafe operator `?->`.
  - **Array Key Shielding**: Always shield array key accesses (especially on superglobals like `$_GET`, `$_POST`, `$_SERVER`, and `$_COOKIE`) using `??` to prevent `E_WARNING` warnings on PHP 8.0+.
  - **Variable Initialization**: Always initialize array keys before performing operations like accumulation (e.g., `$arr['total'] = 0` before `$arr['total'] += ...`).
- **Autoloading**: Do not rely on Composer's autoloader for the project's own classes. The custom autoloader in `lib/include.php` automatically maps class names to filenames in `src/` and `lib/`.

### Frontend
- **HTML & PHP Templates**: UI markup is mixed with PHP in `lib/tpl/`.
- **CSS**: Plain, vanilla CSS in `public/css/customer.css`. Avoid adding utility classes or external CSS frameworks unless explicitly requested.
- **JavaScript**: jQuery-based interactive logic in `public/js/customer.js`.

---

## 4. Coding Guidelines & Best Practices

### Configuration
- Never commit credentials or local server configurations to `config.php`.
- When adding a new configuration option, add it to `config.php` with a sensible default, and ensure that the configuration check in `config.php` (which verifies if `config.local.php` is missing any keys) is updated if necessary.

### Modifying Business Logic vs. UI
- **Controller/Action Logic**: Keep all request handling, Beanstalkd communication, and data preparation inside the `Console` class in `lib/include.php`.
- **UI/Templates**: Keep the templates in `lib/tpl/` focused purely on rendering. Avoid database/socket communication or complex business logic inside templates.
- **Sample Jobs**: Sample jobs are stored in `storage.json`. Ensure any modification to storage logic implements `IStorage` and is updated in `src/Storage.php`.

### Code Style
- Follow the existing style:
  - 4-space indentation for PHP, JS, and CSS.
  - `camelCase` for method names (e.g., `getSampleJobs`).
  - Prefix action methods with an underscore (e.g., `_actionKick`).
  - Keep comments clean, concise, and in English.

---

## 5. Development & Running the Project

### Running Locally (PHP Built-in Server)
To run the console locally without Docker or Vagrant, navigate to the project root and run:
```powershell
php -S localhost:8080 -t public
```
Then open `http://localhost:8080` in your browser.

### Running with Docker
To build and run the Docker container:
```powershell
docker compose up --build
```

### Running with Vagrant
To spin up the Vagrant virtual machine:
```powershell
vagrant up
```

---

## 6. Checklist for Changes
Before finalizing any pull request or change:
1. **PHP Compatibility**: Ensure the code remains compatible with the PHP 7.0 to PHP 8.5+ range. Do not introduce PHP 7.4+ or PHP 8.0+ syntax.
2. **PHP 8 Warnings**: Verify that all array key accesses are shielded (e.g. using `??`) and variables are initialized to avoid PHP 8+ `E_WARNING` warnings.
3. **Local Config**: Verify that `config.local.php` is not tracked by Git.
4. **Storage Permission**: Ensure that any changes to sample jobs do not break the writability check of `storage.json` in `src/Storage.php`.
5. **No Console Errors**: Check the browser console to ensure jQuery and `customer.js` run without errors.
