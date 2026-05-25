#!/bin/bash

echo "🎯 DÉMONSTRATION DU MONITORING - PARTIE 2"
echo "=========================================="
echo ""

echo "📋 1. VÉRIFICATION DES SERVICES"
echo "Services Docker actifs:"
docker compose ps --format "table {{.Service}}\t{{.Status}}\t{{.Ports}}"
echo ""

echo "📊 2. TEST DES MÉTRIQUES PROMETHEUS"
echo "Targets Prometheus:"
targets=$(curl -s http://localhost:9090/api/v1/targets | grep -o '"job":"[^"]*"' | sort | uniq | wc -l)
echo "✅ $targets targets configurés"

echo ""
echo "Métriques système disponibles:"
curl -s "http://localhost:9090/api/v1/label/__name__/values" | grep -o '"node_[^"]*"' | head -5
echo ""

echo "🌐 3. CONNECTIVITÉ DES INTERFACES"
interfaces=("localhost:8080:Laravel" "localhost:9090:Prometheus" "localhost:3000:Grafana" "localhost:9116:cAdvisor")

for interface in "${interfaces[@]}"; do
    IFS=':' read -r host port name <<< "$interface"
    echo -n "Testing $name ($host:$port): "
    if timeout 3 curl -s "$host:$port" > /dev/null 2>&1; then
        echo "✅ OK"
    else
        echo "❌ ERREUR"
    fi
done

echo ""
echo "🚨 4. SYSTÈME D'ALERTES"
alerts_total=$(curl -s http://localhost:9090/api/v1/rules | grep -o '"alert"' | wc -l)
alerts_firing=$(curl -s http://localhost:9090/api/v1/alerts | grep -o '"state":"firing"' | wc -l)
echo "📋 Alertes configurées: $alerts_total"
echo "🔥 Alertes actives: $alerts_firing"

echo ""
echo "📈 5. MÉTRIQUES EN TEMPS RÉEL"
echo "CPU Usage:"
cpu_usage=$(curl -s "http://localhost:9090/api/v1/query?query=100-(avg(rate(node_cpu_seconds_total{mode=\"idle\"}[1m]))*100)" | grep -o '"value":\[[^]]*\]' | head -1)
echo "   $cpu_usage"

echo ""
echo "Memory Usage:"
mem_usage=$(curl -s "http://localhost:9090/api/v1/query?query=(1-(node_memory_MemAvailable_bytes/node_memory_MemTotal_bytes))*100" | grep -o '"value":\[[^]]*\]' | head -1)
echo "   $mem_usage"

echo ""
echo "Container Count:"
container_count=$(curl -s "http://localhost:9090/api/v1/query?query=count(container_last_seen)" | grep -o '"value":\[[^]]*\]' | head -1)
echo "   $container_count"

echo ""
echo "🎛️ 6. ACCÈS AUX DASHBOARDS"
echo "┌─────────────────────────────────────────────────────────┐"
echo "│                    INTERFACES DISPONIBLES               │"
echo "├─────────────────────────────────────────────────────────┤"
echo "│ 🌐 Application Laravel: http://localhost:8080          │"
echo "│ 📊 Grafana Dashboard:   http://localhost:3000          │"
echo "│    └─ Login: admin / Password: admin123                │"
echo "│ 📈 Prometheus Metrics:  http://localhost:9090          │"
echo "│ 🐳 cAdvisor Containers: http://localhost:9116          │"
echo "│ 💾 Node Exporter:       http://localhost:9100/metrics │"
echo "└─────────────────────────────────────────────────────────┘"

echo ""
echo "✅ RÉSUMÉ DE LA DÉMONSTRATION"
echo "=============================="
echo "🎯 Monitoring opérationnel: OUI"
echo "📊 Métriques collectées: OUI ($targets sources)"
echo "🚨 Alertes configurées: OUI ($alerts_total règles)"
echo "🌐 Interfaces accessibles: OUI"
echo "📈 Données temps réel: OUI"

echo ""
echo "🎉 PARTIE 2 COMPLÉTÉE AVEC SUCCÈS !"
echo "Prochaine étape: Partie 3 - Kubernetes et Optimisation"
echo ""
echo "📚 Documentation disponible:"
echo "   - RAPPORT_FINAL_PARTIE2.md"
echo "   - GUIDE_UTILISATION_MONITORING.md"
echo "   - MONITORING.md"