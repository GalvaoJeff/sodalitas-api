#!/bin/sh
set -e

# Espera o MySQL aceitar conexões antes de seguir (evita erro de
# "connection refused" na primeira subida, quando a API sobe mais rápido
# que o banco de dados dentro do compose).
echo "Aguardando o banco de dados em ${DB_HOST:-db}:${DB_PORT:-3306}..."
until php -r "
try {
    new PDO('mysql:host=${DB_HOST:-db};port=${DB_PORT:-3306}', '${DB_USERNAME}', '${DB_PASSWORD}');
} catch (Throwable \$e) {
    exit(1);
}
" > /dev/null 2>&1; do
  sleep 1
done
echo "Banco de dados disponível."

# Garante que existe uma APP_KEY (gera uma só se ainda não houver).
if [ -z "$(php artisan tinker --execute='echo config("app.key");' 2>/dev/null)" ]; then
  php artisan key:generate --force
fi

php artisan migrate --force

# Cria o link público de storage (para servir avatares/mídias enviadas)
# apenas se ele ainda não existir.
if [ ! -L "public/storage" ]; then
  php artisan storage:link
fi

exec "$@"