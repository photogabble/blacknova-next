# BlackNova Traders - Docker Setup

Quick start guide for running BlackNova Traders in Docker.

## Prerequisites

- Docker
- Docker Compose

## Quick Start

1. **Set your user ID and group ID:**
   ```bash
   # Create .env file with your UID/GID in the docker folder
   cd docker
   echo "USER_ID=$(id -u)" > .env
   echo "GROUP_ID=$(id -g)" >> .env
   ```

   Or copy `.env.example` and edit manually:
   ```bash
   cd docker
   cp .env.example .env
   ```

2. **Build and start containers:**
   ```bash
   cd docker
   docker compose up -d --build
   ```

3. **Access the application:**
   - Open browser to: http://localhost:8080
   - The first run will initialise the database

4. **Create universe (first time setup):**
   - Navigate to: http://localhost:8080/create_universe.php
   - Follow the setup wizard to initialise the game state

5. **View logs:**
   ```bash
   cd docker
   docker compose logs -f web
   ```

## Container Details

- **Web**: PHP 7.4 + Apache on port 8080
- **Database**: MySQL 5.7 on port 3306
  - Database: `bnt`
  - User: `bnt`
  - Password: `bnt`

## Useful Commands

**Stop containers:**
```bash
cd docker && docker compose down
```

**Rebuild after changes:**
```bash
cd docker && docker compose up -d --build
```

**Access MySQL:**
```bash
cd docker && docker compose exec db mysql -u bnt -pbnt bnt
```

**Access web container shell:**
```bash
cd docker && docker compose exec web bash
```

**Reset database (fresh start):**
```bash
cd docker && docker compose down -v && docker compose up -d
```

## Development Mode

To enable PHP error reporting, create a file named `dev` in the project root:
```bash
touch dev
```

## Notes

- Database schema is automatically imported on first run from `schema/mysql/`
- Application files are mounted as volumes for live editing
- Database persists in Docker volume `db_data`
- Composer dependencies are installed automatically on the first container start (see `docker/docker-entrypoint.sh`)
- The `vendor/` directory should not be committed to git (it's in `.dockerignore`)
