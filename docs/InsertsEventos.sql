-- =======================================================
-- 1. POBLAR PERMISOS
-- =======================================================
INSERT INTO permisos (nombre, descripcion) VALUES
('DASHBOARD', 'Acceso al panel principal y métricas generales'),
('ROLES', 'Gestión de roles de usuario y permisos'),
('USUARIOS', 'Gestión de usuarios del personal interno'),
('EVENTOS', 'Creación y edición de eventos'),
('APROBAR_EVENTOS', 'Aprobación o rechazo de publicaciones de eventos'),
('ASISTENCIAS', 'Control de asistencias y check-in en puerta'),
('CERTIFICADOS', 'Generación y envío masivo/individual de certificados PDF');

-- =======================================================
-- 2. POBLAR ROLES
-- =======================================================
INSERT INTO roles (nombre, activo) VALUES
('ADMINISTRADOR', 1),
('SUPERVISOR', 1),
('OPERADOR', 1);

-- =======================================================
-- 3. ASIGNAR PERMISOS A ROLES
-- =======================================================

-- ADMIN (Acceso Total)
INSERT INTO permisos_roles (rol_nombre, permiso_nombre) VALUES
('ADMINISTRADOR', 'DASHBOARD'),
('ADMINISTRADOR', 'ROLES'),
('ADMINISTRADOR', 'USUARIOS'),
('ADMINISTRADOR', 'EVENTOS'),
('ADMINISTRADOR', 'APROBAR_EVENTOS'),
('ADMINISTRADOR', 'ASISTENCIAS'),
('ADMINISTRADOR', 'CERTIFICADOS');

-- SUPERVISOR (Revisa, aprueba y gestiona)
INSERT INTO permisos_roles (rol_nombre, permiso_nombre) VALUES
('SUPERVISOR', 'DASHBOARD'),
('SUPERVISOR', 'EVENTOS'),
('SUPERVISOR', 'APROBAR_EVENTOS'),
('SUPERVISOR', 'ASISTENCIAS'),
('SUPERVISOR', 'CERTIFICADOS');

-- OPERADOR (Crea borrador y toma asistencia en puerta)
INSERT INTO permisos_roles (rol_nombre, permiso_nombre) VALUES
('OPERADOR', 'DASHBOARD'),
('OPERADOR', 'EVENTOS'),
('OPERADOR', 'ASISTENCIAS');

-- =======================================================
-- 4. POBLAR USUARIOS INICIALES
-- Contraseña para todos los usuarios: "clave"
-- Hash: $2y$10$VNgHI7pLLJc8otgTE/MMX.vLCVejzBT4mWskGyQ5AhA5vj2LSTZHG
-- =======================================================

INSERT INTO usuarios (documento_id, rol_name, username, nombres, apellidos, correo, telefono, password_hash, activo) VALUES
('00000001', 'ADMINISTRADOR', 'admin', 'Administrador', 'Principal', 'admin@evenrity.com', '999888777', '$2y$10$VNgHI7pLLJc8otgTE/MMX.vLCVejzBT4mWskGyQ5AhA5vj2LSTZHG', 1),
('00000002', 'SUPERVISOR', 'supervisor1', 'Carlos', 'Mendoza', 'supervisor@evenrity.com', '999888776', '$2y$10$VNgHI7pLLJc8otgTE/MMX.vLCVejzBT4mWskGyQ5AhA5vj2LSTZHG', 1),
('00000003', 'OPERADOR', 'operador1', 'Ana', 'Torres', 'operador@evenrity.com', '999888775', '$2y$10$VNgHI7pLLJc8otgTE/MMX.vLCVejzBT4mWskGyQ5AhA5vj2LSTZHG', 1);

-- =======================================================
-- 5. EVENTO DE PRUEBA (Opcional para testear la landing)
-- =======================================================
INSERT INTO eventos (titulo, tema, descripcion, fecha_evento, direccion, cupos, precio, registro_username, publicacion, supervisor_username) VALUES
('Conferencia Internacional de Innovación Tech 2026', 'Tecnología', 'Evento sobre arquitectura de software y desarrollo web en tiempo récord.', '2026-09-15 09:00:00', 'Av. Javier Prado Este 456, San Isidro', 100, 50.00, 'operador1', 'APROBADO', 'admin');