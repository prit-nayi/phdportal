# PhD Portal

This project follows a feature-based monolithic PHP architecture. The codebase is organized by features rather than separating all views, controllers, and scripts into separate global directories.

## Project Architecture

### 1. Feature Directories

Features are separated into their own top-level directories (e.g., `/auth`, `/staff`). Each feature directory is self-contained and typically includes:

- **`.php` View Files**: The frontend HTML/PHP pages (e.g., `index.php`, `add.php`, `edit.php`).
- **`api/`**: The backend PHP endpoints specifically handling requests (AJAX/Fetch) for this feature.
- **`js/`**: The frontend JavaScript specifically written for this feature's views.

### 2. Core Shared Directory (`/@`)

The `/@` directory contains all global and shared resources that are used across multiple features:

- **`@/assets/`**: Global CSS styles, global JS scripts, and images.
- **`@/includes/`**: Shared view components like `header.php`, `footer.php`, and `scripts.php`.
- **`@/lib/`**: Core PHP utility libraries such as database connections (`database.php`), authentication (`auth.php`), environment variables (`env.php`), and request/response helpers.
- **`@/db/`**: Database-related files.
- **`@/uploads/`**: Directory for user-uploaded files.
- **`@/cache/`**: Application cache files.

## Environment Variables

The project uses environment variables for configuration. Create a `.env.development` or `.env.production` file based on `.env.example` and configure the following variables:

- **`BASE_URL`**: The public URL path where the application is hosted (e.g., `/phd`). Used for generating URLs.
- **`BASE_DIR`**: The physical directory path relative to the document root (usually matches `BASE_URL`).
- **`DOMAIN`**: The domain name where the application is running (e.g., `localhost`).
- **`SECURE_COOKIES`**: Set to `true` to enable secure (HTTPS-only) cookies, or `false` for local HTTP development.
- **`GOOGLE_CLIENT_ID`**: The OAuth 2.0 Client ID obtained from the Google Cloud Console for authentication.
- **`DB_DSN`**: The PHP Data Source Name (DSN) for connecting to the database (e.g., `mysql:host=localhost;port=3306;dbname=phd_db`).
- **`DB_USER`**: The username used to connect to the database.
- **`DB_PASSWORD`**: The password used to connect to the database.

## How to Add Features

To add a new feature to the project, follow these steps:

1. **Create a Feature Directory**: Create a new folder at the root of the project (e.g., `/students`, `/companies`).
2. **Add Views**: Inside your new folder, create your `.php` view files (like `index.php`).
   - Remember to require the global header and footer from the `@/includes` directory to maintain a consistent layout.
3. **Create the API Endpoints**: Create an `api/` subdirectory within your feature folder. Add `.php` scripts here to handle database operations (Create, Read, Update, Delete) and return JSON responses. Use the libraries in `@/lib` for database interactions and responses.
4. **Add Frontend Scripts**: Create a `js/` subdirectory within your feature folder. Add your Javascript files here to handle DOM manipulation, form submissions, and making `fetch()` requests to your local `api/` endpoints.
