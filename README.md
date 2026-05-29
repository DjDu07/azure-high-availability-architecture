# Arquitectura Web Tolerante a Fallos en Microsoft Azure ☁️

Este repositorio contiene el código fuente y documenta el diseño de una arquitectura web tolerante a fallos para una empresa, implementada en Microsoft Azure.

## 🚀 Arquitectura de Infraestructura
De acuerdo con el diseño del proyecto, el sistema integra los siguientes componentes para garantizar alta disponibilidad:
- **Balanceador de Carga (Load Balancer):** Regula el tráfico de red entre las instancias. En caso de que la máquina virtual principal (VM1) se vea saturada, se encarga de distribuir las nuevas entradas a la VM2 configurada.
- **Virtual Machine Scale Set (VMSS):** Conjunto de máquinas virtuales idénticas que permite el escalamiento automático según la demanda.
- **Azure Front Door:** Servicio que proporciona balanceo de carga global y aceleración de aplicaciones.
- **WAF (Web Application Firewall):** Filtra y monitorea el tráfico HTTP para proteger la aplicación web de ataques comunes.
- **Azure Database for MySQL (Flexible Server):** Base de datos administrada en la cual se crearon las tablas para el tratamiento de datos y que se conecta de forma segura a las máquinas virtuales.

## 🛠️ Stack Tecnológico Utilizado
- **Proveedor Cloud:** Microsoft Azure
- **Servidor Web:** Apache
- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP (procesamiento de pedidos y conexión segura a la base de datos)
- **Base de Datos:** MySQL Flexible Server

## 📂 Estructura del Repositorio
El código adjunto corresponde a la capa de aplicación web que se despliega sobre las instancias de cómputo (VMs) de esta arquitectura.
