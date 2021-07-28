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
/*
ALTER TABLE `tbl_alumnos` ADD `alu_password` VARCHAR(255) NULL AFTER `usu_id`;

CREATE TABLE `sch_alumno`.`tbl_plantilla_imagen` ( `pim_id` INT NOT NULL AUTO_INCREMENT , `emp_id` INT NOT NULL , `pim_nombre` VARCHAR(255) NOT NULL , `pim_dir` VARCHAR(255) NOT NULL , `estado` BOOLEAN NOT NULL DEFAULT TRUE , `created_at` TIMESTAMP NULL , `updated_at` TIMESTAMP NULL , `usu_id` INT NOT NULL , PRIMARY KEY (`pim_id`)) ENGINE = InnoDB;

CREATE TABLE `sch_alumno`.`tbl_novedades_sistema` ( `nsi_id` INT NOT NULL AUTO_INCREMENT , `nsi_titulo` VARCHAR(255) NOT NULL , `nsi_descripcion` VARCHAR(255) NULL , `nsi_cuerpo` LONGTEXT NULL , `created_at` INT NULL , `updated_at` INT NULL , `estado` BOOLEAN NOT NULL DEFAULT TRUE , `usu_id` INT NOT NULL , PRIMARY KEY (`nsi_id`)) ENGINE = InnoDB;

CREATE TABLE `sch_alumno`.`tbl_novedad_usuario` ( `nus_id` INT NOT NULL AUTO_INCREMENT , `nsi_id` INT NOT NULL , `usu_id` INT NOT NULL , `estado` BOOLEAN NOT NULL DEFAULT TRUE , `nus_visto` DATETIME NULL , `created_at` TIMESTAMP NULL , `updated_at` TIMESTAMP NULL , PRIMARY KEY (`nus_id`)) ENGINE = InnoDB;

CREATE TABLE `sch_alumno`.`tbl_documentos` ( `doc_id` INT NOT NULL AUTO_INCREMENT , `doc_titulo` VARCHAR(255) NOT NULL , `doc_descripcion` VARCHAR(255) NULL , `doc_cuerpo` LONGTEXT NULL , `estado` BOOLEAN NOT NULL DEFAULT TRUE , `created_at` TIMESTAMP NULL , `updated_at` TIMESTAMP NULL , `usu_id` INT NOT NULL , `doc_id_documento` INT NULL , PRIMARY KEY (`doc_id`)) ENGINE = InnoDB;

ALTER TABLE `tbl_novedades_sistema` ADD `nsi_mostrar` BOOLEAN NOT NULL DEFAULT FALSE AFTER `nsi_cuerpo`;

ALTER TABLE `tbl_plantillas` CHANGE `pla_cuerpo` `pla_cuerpo` TEXT CHARACTER SET utf8 COLLATE utf8_spanish_ci NULL;

*/
/*
CREATE TABLE `sch_alumno`.`tbl_alumno_sede` ( `ase_id` INT NOT NULL AUTO_INCREMENT , `alu_id` INT NOT NULL , `sed_id` INT NOT NULL , `estado` BOOLEAN NOT NULL DEFAULT TRUE , `created_at` TIMESTAMP NULL , `updated_at` TIMESTAMP NULL , `usu_id` INT NOT NULL , PRIMARY KEY (`ase_id`)) ENGINE = InnoDB;

INSERT INTO tbl_alumno_sede (alu_id, sed_id, usu_id,created_at,updated_at) 
SELECT DISTINCT alu_id, sed_id, usu_id,created_at,updated_at  FROM tbl_alumnos;
*/
/*
CREATE TABLE `sch_alumno`.`tbl_tipo_mesa_docente` ( `tmd_id` INT NOT NULL AUTO_INCREMENT , `tmd_nombre` VARCHAR(255) NOT NULL , `estado` BOOLEAN NOT NULL DEFAULT TRUE , PRIMARY KEY (`tmd_id`)) ENGINE = InnoDB;

INSERT INTO `tbl_tipo_mesa_docente` (`tmd_id`, `tmd_nombre`, `estado`) VALUES (NULL, 'Presidente', '1'), (NULL, 'Vocal 1', '1'), (NULL, 'Vocal 2', '1');

CREATE TABLE `sch_alumno`.`tbl_mesa_materia_docente` ( `mmd_id` INT NOT NULL AUTO_INCREMENT , `usu_id` INT NOT NULL , `mma_id` INT NOT NULL , `tmd_id` INT NOT NULL DEFAULT '1' , `created_at` TIMESTAMP NULL , `updated_at` TIMESTAMP NULL , `estado` BOOLEAN NOT NULL DEFAULT TRUE , PRIMARY KEY (`mmd_id`)) ENGINE = InnoDB;

CREATE TABLE `sch_alumno`.`tbl_comision_docente` ( `cdo_id` INT NOT NULL AUTO_INCREMENT , `usu_id` INT NOT NULL , `com_id` INT NOT NULL , `created_at` TIMESTAMP NULL , `updated_at` TIMESTAMP NULL , `estado` BOOLEAN NOT NULL DEFAULT TRUE , `cdo_observaciones` VARCHAR(255) NULL , PRIMARY KEY (`cdo_id`)) ENGINE = InnoDB;

ALTER TABLE `tbl_mesa_materia_docente` ADD `mmd_observaciones` VARCHAR(255) NULL AFTER `tmd_id`;

DELIMITER //
DROP FUNCTION IF EXISTS asistencia_promedio //
CREATE FUNCTION asistencia_promedio(id_comision INTEGER,id_alumno INTEGER)
	RETURNS DECIMAL(10,2)
	BEGIN
		DECLARE estado INTEGER;
		DECLARE prom DECIMAL(10,2);
		select asa.estado, AVG( IF (asa.taa_id = 4,1,0 ) ) as avg
		INTO estado, prom
		from tbl_asistencia_alumno asa
		right join tbl_asistencias asi on asa.asi_id = asi.asi_id
		where
		asa.alu_id = id_alumno and
		asi.com_id = id_comision AND
		asa.estado = 1
		group by asa.estado;
		RETURN prom;
	END //

DELIMITER ;

ALTER TABLE `tbl_mesa_alumno_materia` ADD `mam_adeuda` BOOLEAN NOT NULL DEFAULT FALSE AFTER `mam_observaciones`;
*/
/*
CREATE TABLE `sch_alumno`.`password_resets` ( `email` VARCHAR(255) NOT NULL , `token` VARCHAR(255) NOT NULL , `created_at` TIMESTAMP NULL , UNIQUE (`email`)) ENGINE = InnoDB;
*/
/*
CREATE TABLE `sch_alumno`.`tbl_reporte_job` ( `rjo_id` INT NOT NULL AUTO_INCREMENT , `rjo_cantidad` INT NOT NULL , `rjo_contador` INT NOT NULL DEFAULT '0' , `rjo_ruta` VARCHAR(255) NULL , `rjo_dir` VARCHAR(255) NULL , `usu_id` INT NULL , `rjo_terminado` TIMESTAMP NULL , `created_at` TIMESTAMP NULL , `updated_at` TIMESTAMP NULL , `estado` BOOLEAN NOT NULL DEFAULT TRUE , `rjo_nombre` VARCHAR(255) NOT NULL , PRIMARY KEY (`rjo_id`)) ENGINE = InnoDB;
ALTER TABLE `tbl_reporte_job` ADD `sed_id` INT NOT NULL AFTER `rjo_terminado`;
ALTER TABLE `tbl_mesa_alumno_materia` ADD `mam_nota_final` INT NULL AFTER `mam_nota_nombre`, ADD `mam_nota_final_nombre` VARCHAR(255) NULL AFTER `mam_nota_final`;
*/
/*
ALTER TABLE `tbl_mesa_materia` CHANGE `mma_folio` `mma_folio_libre` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_spanish_ci NULL DEFAULT NULL;
ALTER TABLE `tbl_mesa_materia` ADD `mma_folio_promocion` VARCHAR(255) NULL AFTER `mma_folio_libre`, ADD `mma_folio_regular` VARCHAR(255) NULL AFTER `mma_folio_promocion`;
*/
/*
ALTER TABLE `tbl_diarias` ADD `usu_id` INT NULL AFTER `dia_saldo_otros`;
ALTER TABLE `tbl_diarias` CHANGE `dia_saldo_otros` `dia_saldo_otros` DECIMAL(10,2) NOT NULL DEFAULT '0';
ALTER TABLE `tbl_diarias` ADD `usu_id_cierre` INT NULL AFTER `usu_id`;

ALTER TABLE `tbl_planes_pago` ADD `ppa_cuota_total` DECIMAL(10,2) NOT NULL DEFAULT '0' AFTER `ppa_anio`, ADD `ppa_cuota_cantidad` INT NOT NULL DEFAULT '10' AFTER `ppa_cuota_total`, ADD `ppa_cuota_pagado` INT NOT NULL DEFAULT '0' AFTER `ppa_cuota_cantidad`;
ALTER TABLE `tbl_planes_pago` ADD `ppa_dias_vencimiento` INT NOT NULL DEFAULT '9' AFTER `ppa_cuota_pagado`;
ALTER TABLE `tbl_planes_pago` ADD `ppa_fecha` DATE NULL AFTER `ppa_dias_vencimiento`;
*/
/*
CREATE TABLE `tbl_docente_materia` (
  `dma_id` int(11) NOT NULL,
  `sed_id` int(11) NOT NULL,
  `usu_id` int(11) NOT NULL,
  `mat_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
*/
/*
CREATE TABLE `sch_alumno`.`tbl_dias` ( `dia_id` INT NOT NULL AUTO_INCREMENT , `dia_nombre` VARCHAR(255) NOT NULL , PRIMARY KEY (`dia_id`)) ENGINE = InnoDB;
INSERT INTO `tbl_dias` (`dia_id`, `dia_nombre`) VALUES (NULL, 'Domingo'), (NULL, 'Lunes'), (NULL, 'Martes'), (NULL, 'Miercoles'), (NULL, 'Jueves'), (NULL, 'Viernes'), (NULL, 'Sabado');
CREATE TABLE `sch_alumno`.`tbl_comision_horario` ( `cho_id` INT NOT NULL AUTO_INCREMENT , `com_id` INT NOT NULL , `dia_id` INT NOT NULL , `cho_hora_inicial` TIME NOT NULL , `cho_hora_final` TIME NOT NULL , `created_at` TIMESTAMP NULL , `updated_at` TIMESTAMP NULL , `estado` BOOLEAN NOT NULL DEFAULT TRUE , PRIMARY KEY (`cho_id`)) ENGINE = InnoDB;

CREATE TABLE `sch_alumno`.`tbl_aulas` ( `aul_id` INT NOT NULL AUTO_INCREMENT , `sed_id` INT NOT NULL , `aul_numero` INT NOT NULL , `aul_nombre` VARCHAR(255) NULL , `aul_capacidad` INT NOT NULL DEFAULT '0' , `created_at` TIMESTAMP NOT NULL , `updated_at` TIMESTAMP NOT NULL , `estado` BOOLEAN NOT NULL DEFAULT TRUE , PRIMARY KEY (`aul_id`)) ENGINE = InnoDB;
*/
/*
CREATE TABLE `sch_alumno`.`tbl_docente_contrato` ( `dco_id` INT NOT NULL AUTO_INCREMENT , `usu_id` INT NOT NULL , `tco_id` INT NOT NULL , `created_at` TIMESTAMP NULL , `updated_at` TIMESTAMP NULL , `estado` BOOLEAN NOT NULL DEFAULT TRUE , PRIMARY KEY (`dco_id`)) ENGINE = InnoDB;
*/
/*
INSERT INTO `tbl_forma_pago` (`fpa_id`, `fpa_nombre`, `estado`) VALUES (NULL, 'mercadoPago', '1');
*/
/*
CREATE TABLE `tbl_tipo_docente_cargo` (
  `tdc_id` int(11) NOT NULL,
  `tdc_nombre` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

INSERT INTO `tbl_tipo_docente_cargo` (`tdc_id`, `tdc_nombre`, `estado`) VALUES
(1, 'Titular', 1),
(2, 'Adjunto', 1);
ALTER TABLE `tbl_tipo_docente_cargo`
  ADD PRIMARY KEY (`tdc_id`);

ALTER TABLE `tbl_tipo_docente_cargo`
  MODIFY `tdc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `tbl_docente_materia` ADD `tdc_id` INT NULL AFTER `car_id`, ADD `dma_fecha_asignacion` DATE NULL AFTER `tdc_id`, ADD `dma_horas_catedra` INT NULL DEFAULT '0' AFTER `dma_fecha_asignacion`;
*/

composer require "darkaonline/l5-swagger:5.8.*"
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"

/*
ALTER TABLE `tbl_comision_horario` ADD `aul_id` INT NULL AFTER `cho_hora_final`, ADD `cho_nombre` VARCHAR(255) NULL AFTER `aul_id`, ADD `cho_asistencia` BOOLEAN NOT NULL DEFAULT FALSE AFTER `cho_nombre`;

ALTER TABLE `tbl_comisiones` ADD `com_clase_inicio` DATE NULL AFTER `com_cerrado`, ADD `com_clase_final` DATE NULL AFTER `com_clase_inicio`, ADD `com_asistencia` BOOLEAN NOT NULL DEFAULT FALSE AFTER `com_clase_final`;

ALTER TABLE `tbl_asistencias` CHANGE `usu_id` `usu_id` INT(11) NULL;
*/
/*
ALTER TABLE tbl_alumno_archivo CHANGE aar_nombre aar_nombre VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_spanish_ci NULL;
ALTER TABLE tbl_alumno_archivo CHANGE aar_dir aar_dir VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_spanish_ci NULL;
ALTER TABLE `tbl_alumno_archivo` ADD `aar_observaciones` VARCHAR(255) NULL AFTER `aar_nombre`;
*/
/*
composer require laravel-notification-channels/onesignal

ALTER TABLE `tbl_usuarios` ADD `google_id` VARCHAR(255) NULL AFTER `updated_at`, ADD `facebook_id` VARCHAR(255) NULL AFTER `google_id`;

CREATE TABLE `sch_alumno`.`tbl_usuario_dispositivo` ( `udi_id` INT NOT NULL AUTO_INCREMENT , `usu_id` INT NOT NULL , `udi_device_id` VARCHAR(255) NULL , `udi_device_os` INT NULL , `udi_device_model` INT NULL , `udi_manufacturer` INT NULL , `created_at` TIMESTAMP NULL , `updated_at` TIMESTAMP NULL , `estado` BOOLEAN NOT NULL DEFAULT TRUE , PRIMARY KEY (`udi_id`)) ENGINE = InnoDB;

CREATE TABLE `sch_alumno`.`tbl_usuario_asistencia` ( `uas_id` INT NOT NULL AUTO_INCREMENT , `usu_id` INT NOT NULL , `uas_fecha` DATETIME NULL , `uas_latitud` DECIMAL(10,8) NULL , `uas_longitud` DECIMAL(11,8) NULL , `udi_id` INT NOT NULL , `created_at` TIMESTAMP NULL , `updated_at` TIMESTAMP NULL , `estado` BOOLEAN NOT NULL DEFAULT TRUE , PRIMARY KEY (`uas_id`)) ENGINE = InnoDB;

ALTER TABLE `tbl_usuario_asistencia` ADD `sed_id` INT NOT NULL AFTER `usu_id`;

ALTER TABLE `tbl_novedades_sistema` ADD `sed_id` INT NOT NULL DEFAULT '0' AFTER `nsi_id`;

ALTER TABLE `tbl_novedades_sistema` CHANGE `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL;

ALTER TABLE `tbl_novedades_sistema` CHANGE `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL;

ALTER TABLE `tbl_mesas_examen` ADD `mes_notificacion_push` BOOLEAN NOT NULL DEFAULT FALSE AFTER `mes_nombre`, ADD `mes_notificacion_email` BOOLEAN NOT NULL DEFAULT FALSE AFTER `mes_notificacion_push`;
*/

CREATE TABLE `sch_alumno`.`tbl_feriados` ( `fer_id` INT NOT NULL AUTO_INCREMENT , `fer_motivo` VARCHAR(255) NULL , `fer_tipo` VARCHAR(255) NULL , `fer_dia` INT NULL , `fer_mes` INT NULL , `fer_identificador` VARCHAR(255) NULL , `fer_anio` INT NOT NULL , `created_at` TIMESTAMP NULL , `updated_at` TIMESTAMP NULL , `estado` BOOLEAN NOT NULL DEFAULT TRUE , PRIMARY KEY (`fer_id`)) ENGINE = InnoDB;

composer require guzzlehttp/guzzle
ALTER TABLE `tbl_feriados` ADD `fer_fecha` DATE NULL AFTER `fer_anio`;

#ALTER TABLE `tbl_usuario_dispositivo` CHANGE `udi_device_os` `udi_device_os` VARCHAR(255) NULL DEFAULT NULL;
#ALTER TABLE `tbl_usuario_dispositivo` CHANGE `udi_device_model` `udi_device_model` VARCHAR(255) NULL DEFAULT NULL;
#ALTER TABLE `tbl_usuario_dispositivo` CHANGE `udi_manufacturer` `udi_manufacturer` VARCHAR(255) NULL DEFAULT NULL;
/*
INSERT INTO `tbl_tipo_alumno_documentacion` (`tad_id`, `tad_nombre`, `estado`) VALUES (NULL, 'Certificado de Nacimiento', '1');
*/

#CREATE TABLE `sch_alumno`.`tbl_alumno_dispositivo` ( `adi_id` INT NOT NULL AUTO_INCREMENT , `alu_id` INT NOT NULL , `adi_device_id` VARCHAR(255) NULL , `adi_device_os` VARCHAR(255) NULL , `adi_device_model` VARCHAR(255) NULL , `adi_manufacturer` VARCHAR(255) NULL , `created_at` TIMESTAMP NULL , `updated_at` TIMESTAMP NULL , `estado` BOOLEAN NOT NULL DEFAULT TRUE , PRIMARY KEY (`adi_id`)) ENGINE = InnoDB;

### 2019-12-27
#CREATE TABLE `sch_alumno`.`tbl_tipo_inscripcion_abandono` ( `tia_id` INT NOT NULL AUTO_INCREMENT , `tia_nombre` VARCHAR(255) NOT NULL , `tia_descripcion` TEXT NULL , `created_at` TIMESTAMP NULL , `updated_at` TIMESTAMP NULL , `estado` BOOLEAN NOT NULL DEFAULT TRUE , `usu_id` INT NOT NULL , PRIMARY KEY (`tia_id`)) ENGINE = InnoDB;

#CREATE TABLE `sch_alumno`.`tbl_inscripcion_abandono` ( `iab_id` INT NOT NULL AUTO_INCREMENT , `ins_id` INT NOT NULL , `tia_id` INT NOT NULL , `usu_id` INT NOT NULL , `created_at` TIMESTAMP NULL , `updated_at` TIMESTAMP NULL , `estado` BOOLEAN NOT NULL DEFAULT TRUE , PRIMARY KEY (`iab_id`)) ENGINE = InnoDB;

#UPDATE `tbl_plan_pago_precio` SET `ppp_matricula_monto` = '3500', `ppp_cuota_monto` = '3500', `ppp_bonificacion_monto` = '500', `deleted_at` = NULL WHERE `tbl_plan_pago_precio`.`ppp_id` = 1

### 2020-02-19
/*
ALTER TABLE `tbl_tipo_materia_regimen` ADD `tmr_nombre_corto` VARCHAR(255) NULL AFTER `tmr_nombre`;
UPDATE `tbl_tipo_materia_regimen` SET `tmr_nombre_corto` = 'Cuat.' WHERE `tbl_tipo_materia_regimen`.`tmr_id` = 1;
UPDATE `tbl_tipo_materia_regimen` SET `tmr_nombre_corto` = 'An.' WHERE `tbl_tipo_materia_regimen`.`tmr_id` = 2;

ALTER TABLE `tbl_tipo_materia_lectivo` ADD `tml_nombre_corto` VARCHAR(255) NULL AFTER `tml_nombre`;

UPDATE `tbl_tipo_materia_lectivo` SET `tml_nombre_corto` = '1°' WHERE `tbl_tipo_materia_lectivo`.`tml_id` = 1;
UPDATE `tbl_tipo_materia_lectivo` SET `tml_nombre_corto` = '2°' WHERE `tbl_tipo_materia_lectivo`.`tml_id` = 2;
UPDATE `tbl_tipo_materia_lectivo` SET `tml_nombre_corto` = '3°' WHERE `tbl_tipo_materia_lectivo`.`tml_id` = 3;
UPDATE `tbl_tipo_materia_lectivo` SET `tml_nombre_corto` = '4°' WHERE `tbl_tipo_materia_lectivo`.`tml_id` = 4;
UPDATE `tbl_tipo_materia_lectivo` SET `tml_nombre_corto` = '5°' WHERE `tbl_tipo_materia_lectivo`.`tml_id` = 5;
*/

### 2020-04-05
/*
ALTER TABLE `tbl_notificaciones` ADD `not_puede_email` BOOLEAN NOT NULL DEFAULT TRUE AFTER `not_responder_nombre`, ADD `not_puede_push` BOOLEAN NOT NULL DEFAULT TRUE AFTER `not_puede_email`;
composer require alymosul/laravel-exponent-push-notifications
*/
### 2020-04-15
/*
ALTER TABLE `tbl_inscripciones` 
	ADD `tml_id` INT NOT NULL DEFAULT '1' AFTER `ins_fecha_egreso`,
	ADD `ins_porcentaje_aprobados` DECIMAL(10,2) NOT NULL DEFAULT '0' AFTER `tml_id`,
	ADD `ins_final_total` INT NOT NULL DEFAULT '0' AFTER `ins_porcentaje_aprobados`,
	ADD `ins_final_total_aprobados` INT NOT NULL DEFAULT '0' AFTER `ins_final_total`;

ALTER TABLE `tbl_inscripciones` 
	ADD `ins_final_promedio` TINYINT NOT NULL DEFAULT '0' AFTER `ins_final_total_aprobados`, 
	ADD `ins_final_promedio_aprobados` TINYINT NOT NULL DEFAULT '0' AFTER `ins_final_promedio`;
composer require pusher/pusher-php-server "~4.0"
*/

### 2020-04-22
/*
ALTER TABLE `tbl_docente_materia` ADD `dma_id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`dma_id`);
php artisan migrate --path=/database/migrations/2020_04_22_111834_create_tbl_tipo_docente_estado_table.php
php artisan migrate --path=/database/migrations/2020_04_22_111936_create_tbl_docente_estado_table.php
composer dump-autoload
php artisan db:seed --class=TipoDocenteEstadoSeeder
*/

### 2020-04-27
/*
ALTER TABLE `tbl_docentes` ADD `tde_id` INT NOT NULL DEFAULT '2' AFTER `tco_id`;
*/

### 2020-05-02
composer require binarytorch/larecipe:2.1.3
php artisan larecipe:install

### 2020-05-07

ALTER TABLE `tbl_movimientos` ADD `mov_numero_transaccion` VARCHAR(255) NULL AFTER `mov_numero`;
INSERT INTO `tbl_forma_pago` (`fpa_id`, `fpa_nombre`, `estado`) VALUES (NULL, 'Tranferencia bancaria', '1');

### 2020-07-25

ALTER TABLE `tbl_becas` ADD `bec_porcentaje_matricula` DECIMAL(10,2) NOT NULL DEFAULT '0' AFTER `bec_porcentaje`;
ALTER TABLE `tbl_planes_pago` ADD `bec_id` INT NULL AFTER `sed_id`, ADD `ppa_matricula_original_monto` DECIMAL(10,2) NOT NULL DEFAULT '0' AFTER `bec_id`, ADD `ppa_cuota_original_monto` DECIMAL(10,2) NOT NULL DEFAULT '0' AFTER `ppa_matricula_original_monto`;
ALTER TABLE `tbl_planes_pago` ADD `ppp_id` INT NULL AFTER `bec_id`;

### 2020-08-08


CREATE TABLE `mp_payments` ( 
  `id` INT NOT NULL AUTO_INCREMENT ,
  `obl_id` INT NOT NULL , 
  `ins_id` INT NOT NULL , 
  `preference_id` VARCHAR(191) NULL , 
  `preference_url` VARCHAR(191) NULL , 
  `email` VARCHAR(191) NULL , 
  `monto` DECIMAL(10,2) NULL , 
  `payment_id` VARCHAR(191) NULL , 
  `payment_status` VARCHAR(191) NULL , 
  `estado` BOOLEAN NOT NULL DEFAULT TRUE , 
  `created_at` TIMESTAMP NULL , 
  `updated_at` TIMESTAMP NULL , 
  `observaciones` TEXT NULL , 
  `fecha_pagado` DATETIME NULL , PRIMARY KEY (`id`)
  ) ENGINE = InnoDB;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `mp_ipn` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mp_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `topic` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `mp_weebhooks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mp_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `live_mode` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_created` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `application_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `api_version` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `route` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `route_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `mp_ipn`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mp_weebhooks`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `mp_ipn`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `mp_weebhooks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

### 2020-08-16
php artisan migrate --path=database/migrations/2020_08_16_231554_create_inscripcion_estado_table.php

### 2020-09-03

composer require "mercadopago/dx-php:2.0.0"

### 2020-09-08

ALTER TABLE `mp_payments` CHANGE `obl_id` `obl_id` INT(11) NULL;

### 2020-10-20

ALTER TABLE `tbl_sedes` ADD `sed_mercadopago` BOOLEAN NOT NULL DEFAULT FALSE AFTER `sed_room_id`;

### 2021-06-05

ALTER TABLE `tbl_comisiones` ADD `com_aula_virtual_id` VARCHAR(255) NULL AFTER `sed_id`;

### 2021-06-24

ALTER TABLE `tbl_comision_examen` ADD `cex_examen_virtual_id` VARCHAR(255) NULL AFTER `com_id`;

### 2021-07-06

ALTER TABLE `tbl_materias` ADD `mat_aula_virtual_id` VARCHAR(255) NULL AFTER `usu_id`, ADD `mat_examen_virtual_id` VARCHAR(255) NULL AFTER `mat_aula_virtual_id`;

ALTER TABLE `tbl_mesa_materia` ADD `mma_examen_virtual_id` VARCHAR(255) NULL AFTER `mma_alumnos_cantidad_no_aprobado`;
