/*
CREATE TABLE `tbl_plan_pago_precio` (
	`ppp_id` INT NOT NULL AUTO_INCREMENT,
	`ppp_matricula_monto` DECIMAL(10,2) NOT NULL DEFAULT '0',
	`ppp_cuota_monto` DECIMAL(10,2) NOT NULL DEFAULT '0',
	`ppp_interes_monto` DECIMAL(10,2) NOT NULL DEFAULT '0',
	`ppp_bonificacion_monto` DECIMAL(10,2) NOT NULL DEFAULT '0',
	`sed_id` INT NOT NULL,
	`usu_id` INT NOT NULL,
	`usu_id_baja` INT NULL,
	`estado` TINYINT NOT NULL DEFAULT '1',
	`created_at` TIMESTAMP NULL,
	`updated_at` TIMESTAMP NULL,
	`deleted_at` TIMESTAMP NULL,
	PRIMARY KEY (`ppp_id`)
)
COLLATE='utf8_spanish_ci'
;
*/
INSERT INTO `tbl_plan_pago_precio` (`ppp_id`, `ppp_matricula_monto`, `ppp_cuota_monto`, `ppp_interes_monto`, `ppp_bonificacion_monto`, `sed_id`, `usu_id`, `usu_id_baja`, `estado`, `created_at`, `updated_at`, `deleted_at`) VALUES (NULL, '2400', '2400', '100', '200', '0', '0', NULL, '1', '2019-03-02 00:00:00', '2019-03-02 00:00:00', NULL);
/*
ALTER TABLE `tbl_sedes`
	ADD COLUMN `sed_mesa_numero` INT NOT NULL DEFAULT '0' AFTER `sed_pago_numero`;


CREATE TABLE `tbl_tipo_condicion_alumno` (
  `tca_id` int(11) NOT NULL,
  `tca_nombre` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

INSERT INTO `tbl_tipo_condicion_alumno` (`tca_id`, `tca_nombre`, `estado`) VALUES
(1, 'Libre', 1),
(2, 'Promocion', 1),
(3, 'Regular', 1);

ALTER TABLE `tbl_mesa_alumno_materia` ADD `mam_nota_nombre` VARCHAR(255) NULL AFTER `mam_nota`;

ALTER TABLE `tbl_mesa_alumno_materia` ADD `tca_id` INT NOT NULL DEFAULT '1' AFTER `mam_observaciones`;

ALTER TABLE `tbl_mesa_materia` ADD `mma_fecha_cierre` DATETIME NULL AFTER `mma_fecha`;

ALTER TABLE `tbl_mesa_materia` ADD `mma_alumnos_cantidad_aprobado` INT NOT NULL DEFAULT '0' AFTER `mma_observaciones`, ADD `mma_alumnos_cantidad_no_aprobado` INT NOT NULL DEFAULT '0' AFTER `mma_alumnos_cantidad_aprobados`;
*/
/*
ALTER TABLE `tbl_planes_pago` CHANGE `created_at` `created_at` TIMESTAMP on update CURRENT_TIMESTAMP NULL;

ALTER TABLE `tbl_planes_pago` CHANGE `updated_at` `updated_at` TIMESTAMP NULL;

ALTER TABLE `tbl_planes_pago` ADD `deleted_at` TIMESTAMP NULL AFTER `updated_at`, ADD `usu_id_baja` INT NULL AFTER `deleted_at`;
*/
/*
ALTER TABLE `tbl_alumnos` ADD `alu_ciudad_nacimiento` VARCHAR(255) NULL AFTER `alu_fecha_nacimiento`;
*/
/*
ALTER TABLE `tbl_alumnos` ADD `deleted_at` TIMESTAMP NULL AFTER `updated_at`, ADD `usu_id_baja` INT NULL AFTER `deleted_at`;
*/
/*
ALTER TABLE `tbl_pagos` ADD `pag_numero_oficial` VARCHAR(255) NULL AFTER `pag_numero`;
*/
/*
CREATE TABLE `sch_alumno`.`tbl_alumno_materia_nota` ( 
	`amn_id` INT NOT NULL AUTO_INCREMENT , 
	`alu_id` INT NOT NULL , 
	`ins_id` INT NOT NULL , 
	`mat_id` INT NOT NULL , 
	`amn_asistencia` BOOLEAN NULL , 
	`amn_nota` INT NULL , 
	`amn_nota_nombre` VARCHAR(255) NULL , 
	`amn_observaciones` VARCHAR(255) NULL , 
	`tca_id` INT NULL DEFAULT '1' , 
	`amn_fecha` DATE NOT NULL , 
	`amn_libro` VARCHAR(255) NULL , 
	`amn_folio` VARCHAR(255) NULL , 
	`estado` BOOLEAN NOT NULL DEFAULT TRUE , 
	`usu_id` INT NOT NULL , 
	`created_at` TIMESTAMP NULL , 
	`updated_at` TIMESTAMP NULL , PRIMARY KEY (`amn_id`)
	) ENGINE = InnoDB;

*/
/*
ALTER TABLE `tbl_mesa_materia`
	ADD COLUMN `mma_libro` VARCHAR(255) NULL AFTER `usu_id_check_out`,
	ADD COLUMN `mma_folio` VARCHAR(255) NULL AFTER `mma_libro`;
*/
/*
ALTER TABLE `tbl_comisiones` ADD `mod_id` INT NOT NULL DEFAULT '1' AFTER `com_responsable_apellido`;

ALTER TABLE `tbl_movimientos` ADD `mov_numero` VARCHAR(255) NULL AFTER `mov_descripcion`, ADD `tco_id` INT NULL AFTER `mov_numero`;

CREATE TABLE `tbl_tipo_comprobantes` (
  `tco_id` int(11) NOT NULL,
  `tco_nombre` varchar(32) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1'
);
INSERT INTO `tbl_tipo_comprobantes` (`tco_id`, `tco_nombre`, `estado`) VALUES
(1, 'FACTURAS A', 1),
(2, 'NOTAS DE DEBITO A', 1),
(3, 'NOTAS DE CREDITO A', 1),
(4, 'RECIBOS A', 0),
(5, 'NOTAS DE VENTA AL CONTADO A', 0),
(6, 'FACTURAS B', 1),
(7, 'NOTAS DE DEBITO B', 1),
(8, 'NOTAS DE CREDITO B', 1),
(9, 'RECIBOS B', 0),
(10, 'NOTAS DE VENTA AL CONTADO B', 0),
(11, 'FACTURAS C', 0),
(12, 'NOTAS DE DEBITO C', 0),
(13, 'NOTAS DE CREDITO C', 0),
(15, 'RECIBOS C', 0),
(16, 'NOTAS DE VENTA AL CONTADO C', 0),
(99, 'RECIBO X', 0);

ALTER TABLE `tbl_tipo_comprobantes`
  ADD PRIMARY KEY (`tco_id`);
*/

/*
INSERT INTO `tbl_tipo_pago` (`tpa_id`, `tpa_nombre`, `estado`) VALUES ('20', 'Pago tramite', '1')

INSERT INTO `tbl_tipo_obligacion` (`tob_id`, `tob_nombre`, `estado`) VALUES ('20', 'Tramite', '1')

ALTER TABLE `tbl_obligaciones` CHANGE `ppa_id` `ppa_id` INT(11) NULL;
*/

/*
ALTER TABLE `tbl_diarias` ADD `dia_saldo_otros` DECIMAL(10,2) NOT NULL DEFAULT '0' AFTER `dia_saldo`;

ALTER TABLE `tbl_diarias` ADD `dia_saldo_otros_anterior` DECIMAL(10,2) NOT NULL DEFAULT '0' AFTER `dia_saldo_anterior`, ADD `dia_total_otros_ingreso` DECIMAL(10,2) NOT NULL DEFAULT '0' AFTER `dia_saldo_otros_anterior`, ADD `dia_total_otros_egreso` DECIMAL(10,2) NOT NULL DEFAULT '0' AFTER `dia_total_otros_ingreso`;

UPDATE `tbl_forma_pago` SET `fpa_nombre` = 'Tarjeta Credito' WHERE `tbl_forma_pago`.`fpa_id` = 3;
INSERT INTO `tbl_forma_pago` (`fpa_id`, `fpa_nombre`, `estado`) VALUES (NULL, 'Tarjeta Debito', '1')
*/
/*
ALTER TABLE `tbl_comisiones` ADD `com_cerrado` BOOLEAN NOT NULL DEFAULT FALSE AFTER `com_responsable_apellido`;
INSERT INTO `tbl_tipo_usuarios` (`tus_id`, `tus_nombre`, `tus_descripcion`, `estado`) VALUES (NULL, 'Docente', 'Consulta de sus Materias, las mesas de examen y asistencias asociadas', '1');
ALTER TABLE `tbl_sedes` ADD `sed_room_id` VARCHAR(255) NULL DEFAULT NULL AFTER `sed_punto_venta`;
UPDATE `tbl_sedes` SET `sed_room_id` = '19439597' WHERE `tbl_sedes`.`sed_id` = 1;
ALTER TABLE `tbl_usuarios` ADD `usu_localidad` VARCHAR(255) NULL AFTER `usu_celular`;
CREATE TABLE `sch_alumno`.`tbl_docentes` ( 
	`usu_id` INT NOT NULL , 
	`doc_titulo` VARCHAR(255) NULL DEFAULT NULL, 
	`tco_id` INT NULL DEFAULT NULL, 
	`doc_cuit` INT NULL DEFAULT NULL, 
	`doc_observaciones` VARCHAR(255) NULL DEFAULT NULL, 
	PRIMARY KEY (`usu_id`)
	) ENGINE = InnoDB;
CREATE TABLE `sch_alumno`.`tbl_tipo_contratos` ( `tco_id` INT NOT NULL AUTO_INCREMENT , `tco_nombre` VARCHAR(255) NOT NULL , `estado` BOOLEAN NOT NULL DEFAULT TRUE , PRIMARY KEY (`tco_id`)) ENGINE = InnoDB;
INSERT INTO `tbl_tipo_contratos` (`tco_id`, `tco_nombre`, `estado`) VALUES (NULL, 'Subencionado', '1'), (NULL, 'Contratado', '1'), (NULL, 'Monotributista', '1');
ALTER TABLE `tbl_docentes` ADD CONSTRAINT `fk_tco_id` FOREIGN KEY (`tco_id`) REFERENCES `tbl_tipo_contratos`(`tco_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
CREATE TABLE `sch_alumno`.`tbl_usuario_archivo` ( `uar_id` INT NOT NULL AUTO_INCREMENT , `usu_id` INT NOT NULL , `uar_nombre` VARCHAR(255) NOT NULL , `uar_dir` VARCHAR(255) NOT NULL , `estado` BOOLEAN NOT NULL DEFAULT TRUE , `created_at` TIMESTAMP NULL , `updated_at` TIMESTAMP NULL , PRIMARY KEY (`uar_id`)) ENGINE = InnoDB;
*/
/*
ALTER TABLE `tbl_comision_alumno` ADD `com_nota` INT NULL AFTER `usu_id`, ADD `tca_id` INT NULL AFTER `com_nota`;
ALTER TABLE `tbl_comision_alumno` ADD CONSTRAINT `fk_tipo_condicion_alumno` FOREIGN KEY (`tca_id`) REFERENCES `tbl_tipo_condicion_alumno`(`tca_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `tbl_comision_alumno` ADD CONSTRAINT `fk_alumno` FOREIGN KEY (`alu_id`) REFERENCES `tbl_alumnos`(`alu_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `tbl_comision_alumno` ADD CONSTRAINT `fk_comision` FOREIGN KEY (`com_id`) REFERENCES `tbl_comisiones`(`com_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `tbl_comision_alumno` ADD `com_observaciones` VARCHAR(255) NULL AFTER `com_nota`;
*/
