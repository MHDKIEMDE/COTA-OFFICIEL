#!/bin/bash
# Script de démarrage des services COTA (Redis + Queue Worker + Scheduler)
# Usage: ./start-services.sh

BACKEND_DIR="$(cd "$(dirname "$0")" && pwd)"
REDIS_BIN="$HOME/.local/bin/redis-server"
REDIS_CLI="$HOME/.local/bin/redis-cli"

echo "=== COTA Services ==="

# 1. Redis
if $REDIS_CLI ping &>/dev/null 2>&1; then
  echo "✅ Redis déjà démarré"
else
  echo "🚀 Démarrage Redis..."
  $REDIS_BIN --daemonize yes --logfile /tmp/redis.log --port 6379
  sleep 1
  $REDIS_CLI ping && echo "✅ Redis OK" || echo "❌ Redis KO"
fi

# 2. Horizon (remplace queue:work — dashboard sur /horizon)
if pgrep -f "horizon" > /dev/null; then
  echo "✅ Horizon déjà en cours"
else
  echo "🚀 Démarrage Horizon..."
  cd "$BACKEND_DIR"
  nohup php artisan horizon \
    >> /tmp/cota-horizon.log 2>&1 &
  echo "✅ Horizon PID: $! — dashboard: http://localhost:8000/horizon"
fi

# 3. Scheduler (cron Laravel)
if pgrep -f "schedule:work" > /dev/null; then
  echo "✅ Scheduler déjà en cours"
else
  echo "🚀 Démarrage Scheduler..."
  cd "$BACKEND_DIR"
  nohup php artisan schedule:work \
    >> /tmp/cota-scheduler.log 2>&1 &
  echo "✅ Scheduler PID: $!"
fi

echo ""
echo "=== Statut ==="
echo "Redis    : $($REDIS_CLI ping 2>/dev/null || echo 'KO')"
echo "Horizon  : $(pgrep -f 'horizon' > /dev/null && echo 'Running → http://localhost:8000/horizon' || echo 'KO')"
echo "Scheduler: $(pgrep -f 'schedule:work' > /dev/null && echo 'Running' || echo 'KO')"
echo ""
echo "Logs:"
echo "  Redis     → /tmp/redis.log"
echo "  Horizon   → /tmp/cota-horizon.log"
echo "  Scheduler → /tmp/cota-scheduler.log"
