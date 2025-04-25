-- Añadir campo de salario_base a la tabla EMPLEADOS
ALTER TABLE EMPLEADOS ADD COLUMN salario_base DECIMAL(10,2) DEFAULT 0.00 AFTER estado_activo;

-- Actualizar la tabla NOMINAS para soportar mejor la gestión de nóminas
ALTER TABLE NOMINAS ADD COLUMN dias_trabajados INT DEFAULT 0 AFTER fecha_final_trabajo;
ALTER TABLE NOMINAS ADD COLUMN horas_extra INT DEFAULT 0 AFTER dias_trabajados;
ALTER TABLE NOMINAS ADD COLUMN tarifa_hora_extra DECIMAL(10,2) DEFAULT 0.00 AFTER horas_extra;
ALTER TABLE NOMINAS ADD COLUMN monto_horas_extra DECIMAL(10,2) DEFAULT 0.00 AFTER tarifa_hora_extra;
ALTER TABLE NOMINAS ADD COLUMN isr DECIMAL(10,2) DEFAULT 0.00 AFTER impuesto;
ALTER TABLE NOMINAS ADD COLUMN imss DECIMAL(10,2) DEFAULT 0.00 AFTER isr;
ALTER TABLE NOMINAS ADD COLUMN afore DECIMAL(10,2) DEFAULT 0.00 AFTER imss;
ALTER TABLE NOMINAS ADD COLUMN infonavit DECIMAL(10,2) DEFAULT 0.00 AFTER afore;
ALTER TABLE NOMINAS ADD COLUMN otras_deducciones DECIMAL(10,2) DEFAULT 0.00 AFTER infonavit;
ALTER TABLE NOMINAS ADD COLUMN pdf_ruta VARCHAR(255) DEFAULT NULL AFTER historial_nomina;

-- Crear una tabla para almacenar los históricos de nómina
CREATE TABLE IF NOT EXISTS HISTORIAL_NOMINA (
  id_historial INT AUTO_INCREMENT PRIMARY KEY,
  id_nomina INT NOT NULL,
  id_empleado INT NOT NULL,
  fecha_generacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  periodo_inicio DATE NOT NULL,
  periodo_fin DATE NOT NULL,
  salario_base DECIMAL(10,2) NOT NULL,
  dias_trabajados INT NOT NULL,
  horas_extra INT DEFAULT 0,
  monto_horas_extra DECIMAL(10,2) DEFAULT 0.00,
  salario_bruto DECIMAL(10,2) NOT NULL,
  isr DECIMAL(10,2) DEFAULT 0.00,
  imss DECIMAL(10,2) DEFAULT 0.00,
  afore DECIMAL(10,2) DEFAULT 0.00,
  infonavit DECIMAL(10,2) DEFAULT 0.00,
  otras_deducciones DECIMAL(10,2) DEFAULT 0.00,
  salario_neto DECIMAL(10,2) NOT NULL,
  pdf_ruta VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (id_nomina) REFERENCES NOMINAS(id_nomina) ON DELETE CASCADE,
  FOREIGN KEY (id_empleado) REFERENCES EMPLEADOS(id_empleado) ON DELETE CASCADE
);

-- Insertar datos de ejemplo para asignar salarios base a los empleados
-- Asignar salarios basados en el rol y departamento, solo para empleados activos con rol "Empleado"
UPDATE EMPLEADOS 
SET salario_base = 
    CASE 
        WHEN id_departamento BETWEEN 1 AND 4 THEN 25000.00  -- Directivos
        WHEN id_departamento BETWEEN 5 AND 8 THEN 18000.00  -- Administrativos
        WHEN id_departamento BETWEEN 9 AND 11 THEN 15000.00 -- Docentes
        WHEN id_departamento BETWEEN 12 AND 14 THEN 14000.00 -- Apoyo
        WHEN id_departamento BETWEEN 15 AND 16 THEN 10000.00 -- Servicios
        ELSE 8000.00 -- Otros
    END
WHERE estado_activo = 1 AND rol = 'Empleado'; 