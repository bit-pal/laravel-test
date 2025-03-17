# Laravel Scoring and Booking System

A Laravel-based system for user matching and call booking functionality.

## Requirements

- PHP >= 8.1
- Composer
- MySQL >= 5.7
- Node.js & NPM (for frontend assets)

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd <project-directory>
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install NPM dependencies:
```bash
npm install
```

4. Create environment file:
```bash
cp .env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Configure your database in `.env` file:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

7. Run migrations:
```bash
php artisan migrate
```

8. Start the development server:
```bash
php artisan serve
```

## API Endpoints

### Authentication
All endpoints require authentication using Laravel Sanctum.

### Scoring System
- `GET /api/matched-users` - Get matched users based on scoring
- `GET /api/matched-co-investors` - Get matched co-investors

### Booking System
- `GET /api/available-slots` - Get available time slots
- `POST /api/book-slot` - Book a specific time slot
- `DELETE /api/cancel-booking` - Cancel a booking

## Features

### Scoring System
- User matching based on preferences
- Support for different user types (Investor, LP/GP, Startup)
- Complex scoring algorithm for user compatibility
- Co-investor matching functionality

### Booking System
- 15-minute slot duration
- Weekly booking limits
- Complex slot availability rules
- Booking cancellation support

## Testing

Run the test suite:
```bash
php artisan test
```
