
## Getting Started

To use this BOM application locally:

1. Download or clone the repository.
2. Install PHP dependencies:

```bash
composer install
```

3. Copy the environment file and update your database settings:

```bash
cp .env.example .env
php artisan key:generate
```

4. Create a fresh database schema and seed the database:

```bash
php artisan migrate:fresh
php artisan db:seed
```

5. Start the local development server:

```bash
php artisan serve
```

Video Link: https://drive.google.com/file/d/1Ls96zFQHxNZ7O75ZhJlIAlfmhCDWihsq/view?usp=drive_link
