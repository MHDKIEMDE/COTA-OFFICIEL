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

# 2. Queue Worker
if pgrep -f "queue:work" > /dev/null; then
  echo "✅ Queue worker déjà en cours"
else
  echo "🚀 Démarrage Queue Worker..."
  cd "$BACKEND_DIR"
  nohup php artisan queue:work redis \
    --queue=default \
    --sleep=3 \
    --tries=3 \
    --timeout=120 \
    >> /tmp/cota-queue.log 2>&1 &
  echo "✅ Queue Worker PID: $!"
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
echo "Redis   : $($REDIS_CLI ping 2>/dev/null || echo 'KO')"
echo "Queue   : $(pgrep -f 'queue:work' > /dev/null && echo 'Running' || echo 'KO')"
echo "Scheduler: $(pgrep -f 'schedule:work' > /dev/null && echo 'Running' || echo 'KO')"
echo ""
echo "Logs:"
echo "  Redis     → /tmp/redis.log"
echo "  Queue     → /tmp/cota-queue.log"
echo "  Scheduler → /tmp/cota-scheduler.log"
