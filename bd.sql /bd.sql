SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

USE appdb;

/* =========================================================
   PROYECTO G5 — SISTEMA DE CITAS MÉDICAS INTELIGENTES CCSS
========================================================= */

/* --- USUARIOS ------------------------------------------- */
CREATE TABLE IF NOT EXISTS g5_users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(100) NOT NULL,
    apellidos  VARCHAR(100) NOT NULL,
    correo     VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,   -- almacenar con password_hash()
    rol        ENUM('paciente','medico','admin') NOT NULL DEFAULT 'paciente'
);

/* --- PERSONAL MÉDICO ------------------------------------- */
CREATE TABLE IF NOT EXISTS g5_personalmedico (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    nombre       VARCHAR(100) NOT NULL,
    especialidad VARCHAR(100) NOT NULL,
    correo       VARCHAR(100)
);

/* --- CITAS ----------------------------------------------- */
CREATE TABLE IF NOT EXISTS g5_citas (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario   INT NOT NULL,
    id_medico    INT,
    especialidad VARCHAR(100) NOT NULL,
    motivo       VARCHAR(200) NOT NULL,
    fecha        DATE         NOT NULL,
    hora         TIME,
    prioridad    ENUM('Alta','Media','Baja') NOT NULL DEFAULT 'Media',
    estado       ENUM('Activa','Confirmada','Cancelada','Reprogramada','Atendida')
                 NOT NULL DEFAULT 'Activa',
    FOREIGN KEY (id_usuario) REFERENCES g5_users(id) ON DELETE CASCADE,
    FOREIGN KEY (id_medico)  REFERENCES g5_personalmedico(id) ON DELETE SET NULL
);

/* --- HISTORIAL MÉDICO ------------------------------------ */
CREATE TABLE IF NOT EXISTS g5_historial_medico (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario   INT NOT NULL,
    paciente     VARCHAR(100) NOT NULL,
    sintomas     VARCHAR(300) NOT NULL,
    diagnostico  VARCHAR(200) NOT NULL,
    tratamiento  VARCHAR(300) NOT NULL,
    notas        TEXT,
    fecha        DATE NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES g5_users(id) ON DELETE CASCADE
);

/* --- DIAGNÓSTICOS (detalle clínico por cita) ------------ */
CREATE TABLE IF NOT EXISTS g5_diagnosticos (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    id_cita      INT,
    id_usuario   INT NOT NULL,
    descripcion  VARCHAR(300) NOT NULL,
    fecha        DATE NOT NULL,
    FOREIGN KEY (id_cita)    REFERENCES g5_citas(id) ON DELETE SET NULL,
    FOREIGN KEY (id_usuario) REFERENCES g5_users(id) ON DELETE CASCADE
);

/* =========================================================
   DATOS DE PRUEBA
   Contraseña de todos los usuarios de prueba: 12345
========================================================= */

-- Hash bcrypt de '12345' generado con password_hash('12345', PASSWORD_DEFAULT)
-- Verificado: password_verify('12345', hash) = true
INSERT INTO g5_users (nombre, apellidos, correo, password, rol) VALUES
('Administrador', 'Sistema',      'admin@ccss.cr',  '$2y$10$C1PwJn0g1U1/dtasgSIJZOskAyVWq/6I48/ePUrGCaTWmmOGbAOTq', 'admin'),
('Carlos',        'Mendoza Ríos', 'carlos@ccss.cr', '$2y$10$C1PwJn0g1U1/dtasgSIJZOskAyVWq/6I48/ePUrGCaTWmmOGbAOTq', 'medico'),
('Sara',          'Pérez Vega',   'sara@ccss.cr',   '$2y$10$C1PwJn0g1U1/dtasgSIJZOskAyVWq/6I48/ePUrGCaTWmmOGbAOTq', 'paciente');

INSERT INTO g5_personalmedico (nombre, especialidad, correo) VALUES
('Dr. Carlos Mendoza', 'Medicina General', 'carlos@ccss.cr'),
('Dra. Ana Solís',     'Pediatría',        'ana@ccss.cr'),
('Dr. Luis Vargas',    'Cardiología',      'luis@ccss.cr');

INSERT INTO g5_citas (id_usuario, id_medico, especialidad, motivo, fecha, hora, prioridad, estado) VALUES
(3, 1, 'Medicina General', 'Dolor de cabeza frecuente', '2026-04-20', '09:00:00', 'Media',  'Activa'),
(3, 2, 'Pediatría',        'Control de rutina',         '2026-04-22', '10:30:00', 'Baja',   'Confirmada'),
(3, 3, 'Cardiología',      'Dolor en el pecho',         '2026-04-18', '08:00:00', 'Alta',   'Atendida');

INSERT INTO g5_historial_medico (id_usuario, paciente, sintomas, diagnostico, tratamiento, notas, fecha) VALUES
(3, 'Sara Pérez', 'Dolor de pecho, dificultad para respirar',
 'Hipertensión leve', 'Enalapril 5mg una vez al día, dieta baja en sodio',
 'Seguimiento en 1 mes', '2026-04-18');
