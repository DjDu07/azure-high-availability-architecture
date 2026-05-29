# Cloud Architecture Deployment: Alta Disponibilidad en Azure ☁️

Este repositorio documenta el diseño, aprovisionamiento y despliegue de una arquitectura web tolerante a fallos y altamente escalable utilizando los servicios en la nube de **Microsoft Azure**.

## 🚀 Arquitectura de Infraestructura (Cloud Computing)
El sistema fue diseñado para garantizar alta disponibilidad, seguridad perimetral y distribución eficiente del tráfico de red a través de los siguientes componentes:
- **Azure Front Door & WAF:** Punto de entrada global que proporciona aceleración de contenido y protección mediante Web Application Firewall contra vulnerabilidades (OWASP Top 10).
- **Balanceador de Carga (Load Balancer):** Distribución equitativa del tráfico HTTP/HTTPS entre las instancias activas para evitar la saturación de los nodos de cómputo.
- **Virtual Machine Scale Sets (VMSS):** Autoescalado dinámico de las máquinas virtuales web (Nginx/Apache) según las métricas de demanda del servidor.
- **Azure Database for MySQL (Flexible Server):** Base de datos relacional administrada, configurada para integrarse de forma segura con las instancias de cómputo.
- **Azure Key Vault:** Bóveda criptográfica para la gestión centralizada y segura de secretos, cadenas de conexión y certificados.
- **Azure Monitor & Application Insights:** Telemetría, registro de eventos y detección de anomalías en tiempo real.

## 🛠️ Stack Tecnológico
- **Cloud Provider:** Microsoft Azure
- **Frontend & Backend:** HTML, CSS, JavaScript, PHP
- **Base de Datos:** MySQL

## 📋 Estructura del Repositorio
El código fuente adjunto representa la capa de aplicación (frontend y scripts de conexión backend) que se despliega sobre esta infraestructura autoescalable.
