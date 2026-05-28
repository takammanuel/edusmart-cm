# EduSmart CM

Projet fullstack composé d'un backend Laravel et d'un frontend React.

## Structure

```
edusmart-cm/
├── backend/    ← API Laravel (PHP)
└── frontend/   ← Application React (Vite)
```

## Installation

### Backend
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### Frontend
```bash
cd frontend
npm install
npm run dev
```

## Identifiants de test

| Rôle       | Email                  | Mot de passe  |
|------------|------------------------|---------------|
| Admin      | admin@edusmart.cm      | password123   |
| Enseignant | teacher@edusmart.cm    | password123   |
