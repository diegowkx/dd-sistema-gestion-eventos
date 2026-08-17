-- =======================================================
-- MÓDULO 1: SEGURIDAD, ROLES Y AUDITORÍA
-- =======================================================

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP(),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE, -- DASHBOARD, ROLES, USUARIOS, EVENTOS, APROBAR_EVENTOS, ASISTENCIAS, CERTIFICADOS
    descripcion VARCHAR(255)
);

CREATE TABLE permisos_roles (
    rol_nombre VARCHAR(50) NOT NULL,
    permiso_nombre VARCHAR(50) NOT NULL,
    PRIMARY KEY (rol_nombre, permiso_nombre),
    FOREIGN KEY (rol_nombre) REFERENCES roles(nombre) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (permiso_nombre) REFERENCES permisos(nombre) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE usuarios (
    documento_id VARCHAR(15) PRIMARY KEY,
    rol_name VARCHAR(50) NOT NULL DEFAULT 'OPERADOR',
    username VARCHAR(20) NOT NULL UNIQUE,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(15),
    password_hash VARCHAR(100) NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP(),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
    FOREIGN KEY (rol_name) REFERENCES roles(nombre) ON UPDATE CASCADE
);

CREATE TABLE iphys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_username VARCHAR(20) NOT NULL,
    rol_name VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    fecha_acceso DATETIME DEFAULT CURRENT_TIMESTAMP(),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP(),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
    FOREIGN KEY (usuario_username) REFERENCES usuarios(username) ON UPDATE CASCADE,
    FOREIGN KEY (rol_name) REFERENCES roles(nombre) ON UPDATE CASCADE
);

-- =======================================================
-- MÓDULO 2: GESTIÓN DE EVENTOS
-- =======================================================

CREATE TABLE eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    tema VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_evento DATETIME NOT NULL,
    direccion VARCHAR(200) NOT NULL,
    cupos INT NOT NULL DEFAULT 0,
    precio DECIMAL(10, 2) NOT NULL DEFAULT 0.00,

    -- Control de Aprobación Interna
    registro_username VARCHAR(20) NOT NULL,
    publicacion ENUM('PENDIENTE', 'APROBADO', 'RECHAZADO') NOT NULL DEFAULT 'PENDIENTE',
    supervisor_username VARCHAR(20) NULL,
    observaciones TEXT NULL,

    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP(),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),

    FOREIGN KEY (registro_username) REFERENCES usuarios(username) ON UPDATE CASCADE,
    FOREIGN KEY (supervisor_username) REFERENCES usuarios(username) ON UPDATE CASCADE,
    INDEX idx_eventos_publicacion (publicacion)
);

CREATE TABLE imagenes_evento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    orden INT NOT NULL DEFAULT 1,
    eliminacion TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP(),

    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    UNIQUE (evento_id, orden),
    INDEX idx_imagenes_evento (evento_id),
    INDEX idx_imagenes_eliminacion (eliminacion)
);

CREATE TABLE entradas_participantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,

    -- Datos del Participante
    documento_id VARCHAR(15) NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL,
    telefono VARCHAR(15),

    -- Marcado de Asistencia (Realizado por Operadores)
    asistencia TINYINT(1) DEFAULT 0,
    fecha_asistencia DATETIME NULL,
    operador_marcado_username VARCHAR(20) NULL,

    -- Estado de Emisión del Certificado
    certificado_emitido TINYINT(1) DEFAULT 0,
    fecha_emision_certificado DATETIME NULL,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP(),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),

    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON UPDATE CASCADE,
    FOREIGN KEY (operador_marcado_username) REFERENCES usuarios(username) ON UPDATE CASCADE,
    
    -- Evita duplicidad de inscripción
    UNIQUE KEY idx_evento_participante (evento_id, documento_id),
    INDEX idx_entradas_evento (evento_id),
    INDEX idx_entradas_documento (documento_id),
    INDEX idx_entradas_asistencia (asistencia)
);