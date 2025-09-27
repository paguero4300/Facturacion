# 💼 QPOS - Sistema de Facturación Electrónica

> Sistema completo de Point of Sale (POS) con facturación electrónica para SUNAT, desarrollado con Laravel y Filament.

## ✨ Características Principales

- 🧾 **Facturación Electrónica**: Integración completa con SUNAT mediante QPSE y Greenter
- 💰 **Punto de Venta**: Interface moderna para ventas rápidas
- 📊 **Gestión de Inventario**: Control de productos, categorías y marcas
- 👥 **Gestión de Clientes**: Administración completa de clientes
- 💱 **Tipos de Cambio**: Actualización automática desde APIs oficiales
- 🎨 **Interface Moderna**: Desarrollado con Filament Admin Panel
- 📄 **Múltiples Formatos**: Facturas A4, Boletas, Tickets 80mm
- 🔌 **Integraciones**: Factiliza, APIs de tipo de cambio, y más

## 🚀 Instalación Rápida

```bash
# Clonar el repositorio
git clone https://github.com/paguero4300/Facturacion.git
cd Facturacion

# Instalar dependencias
composer install
npm install

# Configurar ambiente
cp .env.example .env
php artisan key:generate

# Configurar base de datos y migrar
php artisan migrate --seed

# Compilar assets
npm run build

# Iniciar servidor
php artisan serve
```

## 📚 Documentación

Toda la documentación del proyecto se encuentra organizada en la carpeta [`docs/`](./docs/):

- **[📖 Índice Completo](./docs/INDEX.md)** - Navegación por toda la documentación
- **[⚙️ Configuración QPSE](./docs/QPSE_SETUP.md)** - Configuración inicial del sistema
- **[🔧 Solución de Problemas](./docs/QPSE_CONNECTION_TROUBLESHOOTING.md)** - Troubleshooting
- **[💰 Sistema de Facturación](./docs/INVOICE_PDF_DOCUMENTATION.md)** - Documentación de facturas
- **[🔌 Integraciones](./docs/FACTILIZA_API.md)** - APIs y servicios externos

### Documentación por Categorías:
- 🏗️ **Arquitectura y Configuración**
- 💰 **Sistema de Facturación** 
- 💱 **Tipos de Cambio y APIs**
- 🔌 **Integraciones y APIs**
- 🎨 **Interface y Componentes**
- 🤖 **Desarrollo y AI**

## 🛠️ Stack Tecnológico

### Backend
- **Laravel 11** - Framework PHP
- **Filament 3** - Panel de administración
- **MySQL** - Base de datos
- **Greenter** - Facturación electrónica
- **QPSE** - Integración SUNAT

### Frontend  
- **Tailwind CSS** - Estilos
- **Alpine.js** - Interactividad
- **Iconoir** - Iconografía
- **Flatpickr** - Selectores de fecha

### Integraciones
- **Factiliza API** - Gestión de clientes
- **APIs Tipo de Cambio** - Actualizaciones automáticas
- **SUNAT** - Facturación electrónica oficial

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📝 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para detalles.

## 📞 Soporte

Si tienes preguntas o necesitas ayuda:

- 📖 Consulta la [documentación completa](./docs/INDEX.md)
- 🐛 Reporta bugs en [Issues](https://github.com/paguero4300/Facturacion/issues)
- 💬 Discusiones en [Discussions](https://github.com/paguero4300/Facturacion/discussions)

---

**Desarrollado con ❤️ para facilitar la facturación electrónica en Perú**
