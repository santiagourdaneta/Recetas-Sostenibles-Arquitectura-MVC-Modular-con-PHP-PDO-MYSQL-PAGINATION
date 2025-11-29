**Arquitectura MVC Minimalista, Segura y Escalable en PHP Puro**

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php)]()
[![License](https://img.shields.io/badge/License-MIT-green.svg)]()
[![Status](https://img.shields.io/badge/Status-Stable-brightgreen)]()
[![Author](https://img.shields.io/badge/Author-Santiago%20Urdaneta-blue)](https://github.com/santiagourdaneta)

Este proyecto es una base de aplicación web ligera desarrollada con
**PHP puro**, diseñada bajo una **arquitectura modular MVC
(Modelo-Vista-Controlador)** con un enfoque
en la sostenibilidad.

Incluye una abstracción segura de base de datos mediante **PDO** y paginación integrada.

------------------------------------------------------------------------

## ✨ Características Principales

-   **Abstracción de Base de Datos (PDO)**
-   **Paginación Eficiente**
-   **Patrón MVC Simple**
-   **OOP + Namespaces**
-   **Controladores preparados para AJAX y renderizado parcial**

Abstracción de Base de Datos (PDO):
Clase Database con consultas preparadas, métodos seguros (query(), execute(), fetchColumn()) y prevención contra SQL Injection.

Paginación Eficiente:
Implementada en RecetaModel y gestionada por RecetaController para cargar recetas activas por segmento/página.

Patrón MVC Simple y Limpio:
Separación clara entre:

Modelos → lógica de datos

Controladores → lógica de negocio

Vistas → renderizado

OOP + Namespaces:
Código organizado en clases y espacios de nombres profesionales.

------------------------------------------------------------------------

## 🛠️ Estructura del Código

  ------------------------------------------------------------------------------
  Archivo                  Namespace                Descripción
  ------------------------ ------------------------ ----------------------------
  `Database.php`           `App`                    Conexión y consultas seguras
                                                    vía PDO

  `RecetaModel.php`        `App\Models`             Lógica de datos y paginación

  `RecetaController.php`   `App\Controllers`        Lógica de negocio,
                                                    renderizado
  ------------------------------------------------------------------------------

------------------------------------------------------------------------

## 🚀 Instalación y Uso

### Prerrequisitos

PHP 8.2+, MySQL / MariaDB, Servidor web: Apache / Nginx, Activada la extensión PDO.

### Configuración de Base de Datos

``` sql
CREATE TABLE recetas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    ingredientes_data JSON,
    sostenibilidad_score INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Inicialización en index.php

``` php
$db = new App\Database('localhost', 'nombre_db', 'usuario', 'password');
$recetaModel = new App\Models\RecetaModel($db);
$recetaController = new App\Controllers\RecetaController($recetaModel);
$recetaController->index();
```

🤝 Contribuciones

Las contribuciones son bienvenidas.
Consulta el archivo CONTRIBUTING.md para más detalles.

📄 Licencia

Este proyecto está bajo la licencia MIT.
Consulta el archivo LICENSE.md.

👤 Autor

Santiago Urdaneta
GitHub: https://github.com/santiagourdaneta



