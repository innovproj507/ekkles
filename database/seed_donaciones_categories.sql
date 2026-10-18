-- ============================================================
-- SEED: Categorías de Ingreso y Egreso para Obras Misioneras y Donaciones
-- church_id = 1, target_module = 'donaciones'
-- ENCODING: utf8mb4
-- ============================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Categorías de INGRESO (donaciones)
INSERT INTO `finance_categories` (`church_id`, `name`, `type`, `target_module`, `code`, `is_active`) VALUES
(1, 'Donación de PROSEA', 'ingreso', 'donaciones', NULL, 1),
(1, 'Donación de Iglesia Unión del Área del Canal', 'ingreso', 'donaciones', NULL, 1),
(1, 'Donación de Yolanda Torres', 'ingreso', 'donaciones', NULL, 1),
(1, 'Donación de la Sra. María', 'ingreso', 'donaciones', NULL, 1),
(1, 'Donación de Scott', 'ingreso', 'donaciones', NULL, 1),
(1, 'Donación para canoa de Nelson Chamí', 'ingreso', 'donaciones', NULL, 1),
(1, 'Donación para canoa de Leo', 'ingreso', 'donaciones', NULL, 1),
(1, 'Donación para jóvenes de Cimarrón', 'ingreso', 'donaciones', NULL, 1),
(1, 'Donación McKenzie CoC', 'ingreso', 'donaciones', NULL, 1),
(1, 'Donación Timberlane CoC', 'ingreso', 'donaciones', NULL, 1),
(1, 'Reembolso', 'ingreso', 'donaciones', NULL, 1),
(1, 'Devolución', 'ingreso', 'donaciones', NULL, 1),
(1, 'Donación Luz Marina Velásquez', 'ingreso', 'donaciones', NULL, 1),
(1, 'Donación Park CoC', 'ingreso', 'donaciones', NULL, 1),
(1, 'Donación Mike Vann', 'ingreso', 'donaciones', NULL, 1),
(1, 'Donación Rebeca Kidder', 'ingreso', 'donaciones', NULL, 1),
(1, 'Compra de Medicinas', 'ingreso', 'donaciones', NULL, 1),
(1, 'Monto inicial', 'ingreso', 'donaciones', NULL, 1),
(1, 'Donación', 'ingreso', 'donaciones', NULL, 1),
(1, 'Donación Computadora PROSEA', 'ingreso', 'donaciones', NULL, 1),
(1, 'Donaciones para el equipo de sonido', 'ingreso', 'donaciones', NULL, 1),
(1, 'Donaciones de Jason Meadow para Danitza Cárdenas', 'ingreso', 'donaciones', NULL, 1),
(1, 'Reservación de hoteles', 'ingreso', 'donaciones', NULL, 1),
(1, 'Transporte para giras médicas', 'ingreso', 'donaciones', NULL, 1),
(1, 'Transferencia de cuentas', 'ingreso', 'donaciones', NULL, 1),
(1, 'Intereses', 'ingreso', 'donaciones', NULL, 1);

-- Categorías de EGRESO (donaciones)
INSERT INTO `finance_categories` (`church_id`, `name`, `type`, `target_module`, `code`, `is_active`) VALUES
(1, 'Salario de Nelson Mepaquito', 'egreso', 'donaciones', NULL, 1),
(1, 'Salario de Nelson Chamí', 'egreso', 'donaciones', NULL, 1),
(1, 'Salario de Edwin Isabaré', 'egreso', 'donaciones', NULL, 1),
(1, 'Salario de Euclides Nieto', 'egreso', 'donaciones', NULL, 1),
(1, 'Gastos PROSEA', 'egreso', 'donaciones', NULL, 1),
(1, 'Gastos para canoa de Nelson Chamí', 'egreso', 'donaciones', NULL, 1),
(1, 'Gastos de hoteles', 'egreso', 'donaciones', NULL, 1),
(1, 'Gastos de transporte', 'egreso', 'donaciones', NULL, 1),
(1, 'Donación de comida', 'egreso', 'donaciones', NULL, 1),
(1, 'Compra de medicamentos', 'egreso', 'donaciones', NULL, 1),
(1, 'Comida para Ancoré', 'egreso', 'donaciones', NULL, 1),
(1, 'Misceláneos', 'egreso', 'donaciones', NULL, 1),
(1, 'Encuentro juvenil', 'egreso', 'donaciones', NULL, 1),
(1, 'Reembolso a Nelson', 'egreso', 'donaciones', NULL, 1),
(1, 'Reembolso por materiales para canoa', 'egreso', 'donaciones', NULL, 1),
(1, 'Compra de comida para familias de Cimarrón', 'egreso', 'donaciones', NULL, 1),
(1, 'Transporte en canoa y comida a Cimarron', 'egreso', 'donaciones', NULL, 1),
(1, 'Construcción de la casa de Nelson Mepaquito', 'egreso', 'donaciones', NULL, 1),
(1, 'Transporte hijas de Leo', 'egreso', 'donaciones', NULL, 1),
(1, 'Chamí', 'egreso', 'donaciones', NULL, 1),
(1, 'Mano de obra canoa', 'egreso', 'donaciones', NULL, 1),
(1, 'Escuela Bíblica de Verano', 'egreso', 'donaciones', NULL, 1),
(1, 'Gastos del Proyecto Wellness', 'egreso', 'donaciones', NULL, 1),
(1, 'Universidad de Enerson Mepaquito', 'egreso', 'donaciones', NULL, 1),
(1, 'Sepelio de Luis Amagara', 'egreso', 'donaciones', NULL, 1),
(1, 'Gastos para matrimonios', 'egreso', 'donaciones', NULL, 1),
(1, 'Donaciones de Timberlake Church of Christ', 'egreso', 'donaciones', NULL, 1),
(1, 'Donación', 'egreso', 'donaciones', NULL, 1),
(1, 'Eliester guiro y baños Ancoré', 'egreso', 'donaciones', NULL, 1),
(1, 'Computadora PROSEA', 'egreso', 'donaciones', NULL, 1),
(1, 'Transporte', 'egreso', 'donaciones', NULL, 1),
(1, 'Reembolso', 'egreso', 'donaciones', NULL, 1),
(1, 'Transferencia a JOSIAS de Scott Blackwell', 'egreso', 'donaciones', NULL, 1),
(1, 'Gastos escolares de Danitza Cárdenas', 'egreso', 'donaciones', NULL, 1),
(1, 'Gastos para equipo de sonido', 'egreso', 'donaciones', NULL, 1),
(1, 'Salario de Emiliano Mepaquito', 'egreso', 'donaciones', NULL, 1),
(1, 'Compra comida Sra. María', 'egreso', 'donaciones', NULL, 1),
(1, 'Compra de comida Scott', 'egreso', 'donaciones', NULL, 1),
(1, 'Gastos de comida Iglesia Unión', 'egreso', 'donaciones', NULL, 1),
(1, 'Transferencia de cuentas', 'egreso', 'donaciones', NULL, 1),
(1, 'Comisión del banco', 'egreso', 'donaciones', NULL, 1),
(1, 'Transferencia por equivocación', 'egreso', 'donaciones', NULL, 1);
