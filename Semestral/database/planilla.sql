-- CREATE DATABASE IF NOT EXISTS planilla_prospera
--    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE planilla_prospera;
USE railway;

CREATE TABLE IF NOT EXISTS colaboradores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(150) NOT NULL,
    cedula VARCHAR(25) NOT NULL,
    estado_civil VARCHAR(30) NOT NULL,
    cargo VARCHAR(100) NOT NULL,
    salario_base DECIMAL(10,2) NOT NULL,
    anio_inicio YEAR NOT NULL,
    tipo_declaracion ENUM('Individual', 'Conjunta') NOT NULL DEFAULT 'Individual',
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_colaborador_cedula_nombre (cedula, nombre_completo)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS planillas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT UNSIGNED NOT NULL,
    periodo VARCHAR(80) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    salario_quincenal DECIMAL(10,2) NOT NULL DEFAULT 0,
    bonificacion DECIMAL(10,2) NOT NULL DEFAULT 0,
    cantidad_horas_extra DECIMAL(6,2) NOT NULL DEFAULT 0,
    horas_extras DECIMAL(10,2) NOT NULL DEFAULT 0,
    ventas DECIMAL(12,2) NOT NULL DEFAULT 0,
    porcentaje_comision DECIMAL(6,3) NOT NULL DEFAULT 0,
    comision DECIMAL(10,2) NOT NULL DEFAULT 0,
    dieta DECIMAL(10,2) NOT NULL DEFAULT 0,
    otros_ingresos DECIMAL(10,2) NOT NULL DEFAULT 0,
    salario_bruto DECIMAL(10,2) NOT NULL DEFAULT 0,
    base_cotizable DECIMAL(10,2) NOT NULL DEFAULT 0,
    seguro_social DECIMAL(10,2) NOT NULL DEFAULT 0,
    seguro_educativo DECIMAL(10,2) NOT NULL DEFAULT 0,
    impuesto_renta DECIMAL(10,2) NOT NULL DEFAULT 0,
    otros_descuentos DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_descuentos DECIMAL(10,2) NOT NULL DEFAULT 0,
    salario_neto DECIMAL(10,2) NOT NULL DEFAULT 0,
    css_patrono DECIMAL(10,2) NOT NULL DEFAULT 0,
    se_patrono DECIMAL(10,2) NOT NULL DEFAULT 0,
    riesgo_profesional DECIMAL(10,2) NOT NULL DEFAULT 0,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_planilla_colaborador FOREIGN KEY (colaborador_id)
        REFERENCES colaboradores(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    UNIQUE KEY uq_planilla_colaborador_periodo (colaborador_id, fecha_inicio, fecha_fin)
) ENGINE=InnoDB;

INSERT INTO colaboradores
    (nombre_completo, cedula, estado_civil, cargo, salario_base, anio_inicio, tipo_declaracion)
VALUES
    ('Manuel Peña', '4-590-678', 'Casado', 'Reparador de calle', 690.00, 2024, 'Conjunta'),
    ('José Martínez', '5-789-352', 'Soltero', 'Aseador', 655.20, 2025, 'Individual'),
    ('Federico Montiel', '4-590-678', 'Casado', 'Asistente de gerencia', 900.00, 2023, 'Conjunta'),
    ('Estefanía Sousa Rincón', '8-709-4552', 'Soltera', 'Vendedor supervisor', 655.20, 2024, 'Individual')
ON DUPLICATE KEY UPDATE
    estado_civil = VALUES(estado_civil),
    cargo = VALUES(cargo),
    salario_base = VALUES(salario_base),
    tipo_declaracion = VALUES(tipo_declaracion);
