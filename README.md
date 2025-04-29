# MCQ Test Application

A web application for creating and managing multiple-choice question tests.

## Features

- **Teacher Features**
  - Create and manage MCQ tests
  - Add questions and options
  - View student results and performance

- **Student Features**
  - Take assigned tests
  - Review past test results with detailed feedback
  - Track progress and performance

## Result Page Styling

The test results page features clear visual indicators for correct and incorrect answers:

- **Correct Answers**: Displayed with a light green background and green borders
- **Incorrect Answers**: Displayed with a light red background and red borders 
- Visual badges show "Correct answer" and "Incorrect" labels

## Installation

1. Clone the repository
```bash
git clone https://github.com/username/mcq-shruti.git
cd mcq-shruti
```

2. Install dependencies
```bash
composer install
npm install
```

3. Set up environment
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure database in .env file
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mcq_app
DB_USERNAME=root
DB_PASSWORD=
```

5. Run migrations and seeders
```bash
php artisan migrate
php artisan db:seed
```

6. Run the application
```bash
php artisan serve
npm run dev
```

## Usage

1. Login as a teacher or student
2. Teachers can create tests from the dashboard
3. Students can take tests and view their results